<?php

namespace Modules\Media\Services;

use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Modules\Media\Entities\File;
use Modules\Media\Http\Requests\UploadMediaRequest;
use Modules\Media\Image\Imagy;
use Modules\Media\Jobs\CreateThumbnails;
use Modules\Media\Repositories\FileRepository;
use Modules\Media\ValueObjects\MediaPath;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Validator;

class FileService
{
    use DispatchesJobs;

    /**
     * @var FileRepository
     */
    private $file;

    /**
     * @var Factory
     */
    private $filesystem;

    /**
     * @var Imagy
     */
    private $imagy;

    public function __construct(FileRepository $file, Factory $filesystem, Imagy $imagy)
    {
        $this->file = $file;
        $this->filesystem = $filesystem;
        $this->imagy = $imagy;
    }

    /**
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileExistsException
     */
  public function store(UploadedFile $file, $parentId = 0, $disk = null, $createThumbnails = true)
  {
    $disk = $this->getConfiguredFilesystem($disk);

    $typesWithoutResizeImagesAndCreateThumbnails = config("asgard.media.config.typesWithoutResizeImagesAndCreateThumbnails");

    //validating avaiable extensions
    $request = new UploadMediaRequest(['file' => $file]);
    $validator = Validator::make($request->all(), $request->rules(), $request->messages());
    //if get errors, throw errors
    if ($validator->fails()) {
      $errors = json_decode($validator->errors());
      throw new \Exception(json_encode($errors), 400);
    }
    $savedFile = $this->file->createFromFile($file, $parentId, $disk);

    if (!in_array($savedFile->extension, $typesWithoutResizeImagesAndCreateThumbnails)) {
      $this->resizeImages($file, $savedFile);
    }

    $path = $this->getDestinationPath($savedFile->getRawOriginal('path'));
    $stream = fopen($file->getRealPath(), 'r+');
  
    //call Method delete for all exist in the disk with the same filename
    $this->imagy->deleteAllFor($savedFile);
  
    $organizationPrefix = mediaOrganizationPrefix($savedFile);
  
    $this->filesystem->disk($disk)->writeStream(($organizationPrefix) . $savedFile->path->getRelativeUrl(), $stream, [
      'visibility' => 'public',
      'mimetype' => $savedFile->mimetype,
    ]);

    if (!in_array($savedFile->extension, $typesWithoutResizeImagesAndCreateThumbnails) && $createThumbnails) {
      $this->createThumbnails($savedFile);
    }

    return $savedFile;
  }

  /**
   * @param $path - Url from External
   * @param string $disk - External Name (splash)
   * @return mixed
   */
  public function storeHotLinked($path, $disk = null)
  {

    $data = app("Modules\Media\Services\\".ucfirst($disk)."Service")->getDataFromUrl($path,$disk);

    $data = [
      'filename' => $data['fileName'],
      'path' => $data['path'],
      'extension' => $data['extension'] ?? null,
      'folder_id' => 0,
      'is_folder' => 0,
      'disk' => $disk,
      'mimetype' => $data['mimetype'] ?? null
    ];

    $savedFile = $this->file->create($data);

        return $savedFile;
    }

    /**
     * Resize Images based in the setting defaultImageSize
     */
    private function resizeImages(UploadedFile $file, $savedFile)
    {
      if ($savedFile->isImage()) {
        $image = \Image::make(fopen($file->getRealPath(), 'r+'));
    
        $imageSize = json_decode(setting('media::defaultImageSize', null, config('asgard.media.config.defaultImageSize')));
    
        $image->resize($imageSize->width, $imageSize->height, function ($constraint) {
          $constraint->aspectRatio();
          $constraint->upsize();
        });
    
        $filePath = $file->getPathName();
        \File::put($filePath, $image->stream($savedFile->extension, $imageSize->quality));
      }
    }

    /**
     * Create the necessary thumbnails for the given file
     */
    private function createThumbnails(File $savedFile)
    {
    $this->dispatch(new CreateThumbnails($savedFile));
    }

    private function getDestinationPath($path)
    {
        if ($this->getConfiguredFilesystem() === 'local') {
            return basename(public_path()).$path;
        }

        return $path;
    }

    private function getConfiguredFilesystem($disk = 'publicmedia')
    {
    $settingDisk = setting('media::filesystem', null, config("asgard.media.config.filesystem"));
    if($disk == "publicmedia" && $settingDisk == "s3") return $settingDisk;
    return $disk ?? "publicmedia";
        }

  public function addWatermark($file, $zone){

        //if the watermark zone exist in DB and if is image exclusively
        if (isset($zone->mediaFiles()->watermark->id) && $file->isImage()) {
            //getting watermark file from the DB
            $watermarkFile = File::find($zone->mediaFiles()->watermark->id);

            //if exist the watermark file in the DB
            if (isset($watermarkFile->id)) {
                //watermark file disk
                $watermarkDisk = is_null($watermarkFile->disk) ? $this->getConfiguredFilesystem() : $watermarkFile->disk;

                //file entity disk
                $disk = is_null($file->disk) ? $this->getConfiguredFilesystem() : $file->disk;

                $tenantPrefix = mediaOrganizationPrefix($file);
                //creating image in memory
                $image = \Image::make($this->filesystem->disk($disk)->get(($tenantPrefix).$file->path->getRelativeUrl()));

                // insert watermark at center corner with 0px offset by default
                $image->insert(
                    //file path from specific disk
                    $this->filesystem->disk($watermarkDisk)->path(($tenantPrefix).$watermarkFile->path->getRelativeUrl()),
                    //position inside the base image
                    $zone->options->watermarkPosition ?? 'center',
                    //X axis position
                    $zone->options->watermarkXAxis ?? 0,
                    //Y axis position
                    $zone->options->watermarkYAxis ?? 0
                );

                //put the new file in the same location of the current entity file
                $this->filesystem->disk($disk)->put(($tenantPrefix).$file->path->getRelativeUrl(), $image->stream($file->extension, 100));

                //regenerate thumbnails
                $this->createThumbnails($file);

                //updating entity has_watermark field
                $file->has_watermark = true;

                //saving has_watermark field
                $file->save();
            }
        }
    }
}
