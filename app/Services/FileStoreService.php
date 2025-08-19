<?php

namespace Modules\Imedia\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Validator;

use Modules\Imedia\Models\File;
use Modules\Imedia\Repositories\FileRepository;

//Helpers
use Modules\Imedia\Support\FileHelper;
use Modules\Imedia\Support\ImageHelper;

use Intervention\Image\Laravel\Facades\Image;
use Modules\Imedia\Jobs\CreateThumbnails;

class FileStoreService
{

    private $fileRepository;

    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    /**
     * Usedby: FileApiController
     */
    public function storeFromMultipart(UploadedFile $file, array $options = [], $data = null): File
    {
        //Validations Format
        $this->validationsFile($file);

        //Extra Attributes
        $fileData['originalName'] = $file->getClientOriginalName();
        $fileData['size'] = $file->getFileInfo()->getSize();
        $fileData['visibility'] = $data['visibility'] ?? 'public';
        $fileData['parent_id'] = $data['parent_id'] ?? null;
        //General Method
        return $this->processAndStore($file->getRealPath(), $file->getClientOriginalExtension(), $file->getMimeType(), $options, $fileData);
    }

    /**
     * TODO
     */
    public function storeFromBinary(string $binary, string $extension, string $mimetype, array $options = []): File
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'bin_');
        file_put_contents($tmpPath, $binary);
        return $this->processAndStore($tmpPath, $extension, $mimetype, $options);
    }

    /**
     * TODO
     */
    public function storeFromUrl(string $url, array $options = [], bool $hotlinkOnly = false): File
    {
        if ($hotlinkOnly) {
            return File::create([
                'path' => $url,
                'disk' => null,
                'mimetype' => null,
                'extension' => null,
            ]);
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'url_');
        file_put_contents($tmpPath, file_get_contents($url));
        $info = pathinfo(parse_url($url, PHP_URL_PATH));
        $extension = $info['extension'] ?? 'jpg';
        $mimetype = mime_content_type($tmpPath);

        return $this->processAndStore($tmpPath, $extension, $mimetype, $options);
    }

    /**
     * Process general to create and store
     */
    protected function processAndStore(string $filePath, string $extension, string $mimetype, array $options, $fileData = null): File
    {

        //Get File Name
        $fileName = FileHelper::getSlugToFileName($fileData['originalName']);

        //File Exist so return the one
        $fileExist = $this->checkFileExist($fileName, $fileData['size']);
        if (!is_null($fileExist))
            return $fileExist;

        //Get Data From Options
        $disk = $options['disk'] ?? 's3';
        $imageSize = json_decode(config('imedia.defaultImageSize'));
        $shouldStore = $options['store'] ?? true;
        $generateThumbnails = $options['thumbnails'] ?? true;

        //Exist other with the same name
        $fileName = $this->checkFilenameExist($fileName, $fileData['parent_id'], $disk);

        // Resize if image and max size defined
        if (str_starts_with($mimetype, 'image')) {

            $image =  Image::read($filePath);

            //Scale Image
            $image->scaleDown($imageSize->width, $imageSize->height);

            //Get  Encoded Image
            $encoded = ImageHelper::encodeImage($image, $extension, $imageSize->quality);

            $tmpStream = fopen('php://temp', 'w+');
            fwrite($tmpStream, (string) $encoded);
            rewind($tmpStream);
            $stream = $tmpStream;
        } else {
            //Standar Stream
            $stream = fopen($filePath, 'r');
        }

        //Get Data to Path
        $path = FileHelper::makePath($fileName, $fileData['parent_id']);

        //Create in DB
        $dataToSave = [
            'filename' => $fileName,
            'path' => $path,
            'extension' => substr(strrchr($fileName, '.'), 1),
            'mimetype' => $mimetype,
            'filesize' => $fileData['size'],
            'folder_id' => $fileData['parent_id'],
            'is_folder' => 0,
            'disk' => $disk,
            'visibility' => $fileData['visibility']
        ];


        $file = $this->fileRepository->create($dataToSave);

        //Store in Disk
        if ($shouldStore) {
            Storage::disk($disk)->writeStream($path, $stream, [
                'visibility' => $fileData['visibility'],
                'mimetype' => $mimetype,
            ]);
        }

        //Process Thumbnails
        $typesWithoutResizeImagesAndCreateThumbnails = config("imedia.typesWithoutResizeImagesAndCreateThumbnails");
        if ($generateThumbnails && $file->isImage() && !in_array($file->extension, $typesWithoutResizeImagesAndCreateThumbnails)) {
            CreateThumbnails::dispatch($file);
        }


        return $file;
    }

    /**
     * Validations to File
     */
    private function validationsFile($file)
    {
        $request = new \Modules\Imedia\Http\Requests\CreateFileRequest(["file" => $file]);
        $validator = Validator::make($request->all(), $request->rules(), $request->messages());
        //if get errors, throw errors
        if ($validator->fails()) {
            throw new \Exception(json_encode($validator->errors()), 400);
        }
    }

    /**
     * Check if the File exist
     */
    private function checkFileExist($fileName, $fileSize)
    {

        $params = json_decode(json_encode([
            "filter" => [
                "field" => "filename",
                "extension" => substr(strrchr($fileName, '.'), 1),
                "filesize" => $fileSize,
                "is_folder" => 0
            ]
        ]));

        $fileExist = $this->fileRepository->getItem($fileName, $params);

        return $fileExist;
    }

    /**
     * check File Name exist in other Files
     */
    private function checkFilenameExist($fileName, $parentId, $disk)
    {

        $params = [
            "filter" => [
                "filename" => ["value" => $fileName . "%", "operator" => "like"],
                "disk" => $disk
            ]
        ];

        //Validation Parent
        if (!is_null($parentId)) {
            $params['filter']['folder_id'] = $parentId;
        }

        $filesExist = $this->fileRepository->getItemsBy(json_decode(json_encode($params)));

        if (count($filesExist) > 0) {
            return $this->getNewUniqueFilename($fileName, $filesExist);
        } else {
            return $fileName;
        }
    }

    /**
     * Get Only Unique Filename
     */
    private function getNewUniqueFilename($fileName, $models)
    {
        $fileNameOnly = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        $versionCurrent = $models->reduce(function ($carry, $model) {
            $latestFilename = pathinfo($model->filename, PATHINFO_FILENAME);

            if (preg_match('/_([0-9]+)$/', $latestFilename, $matches) !== 1) {
                return $carry;
            }

            $version = (int)$matches[1];

            return ($version > $carry) ? $version : $carry;
        }, 0);

        return $fileNameOnly . '_' . ($versionCurrent + 1) . '.' . $extension;
    }
}
