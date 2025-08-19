<?php

namespace Modules\Imedia\Services;

use Modules\Imedia\Models\File;
use Illuminate\Support\Facades\Storage;

use Intervention\Image\Laravel\Facades\Image;
use Modules\Imedia\Support\ImageHelper;
use Modules\Imedia\Support\FileHelper;

class ThumbnailService
{

    protected $thumbnailConfigs;

    public function __construct()
    {
        $this->thumbnailConfigs = json_decode(config('imedia.defaultThumbnails'));
    }

    /**
     * Create Thumbnails
     */
    public function generateThumbnails(File $file)
    {

        //Get Base Data
        $disk = $file->disk ?? 's3';
        $stream = Storage::disk($disk)->readStream($file->path);
        $filename = $file->filename;

        //Get Original Image
        $original = Image::read($stream);
        //Delete extension
        $pathInfo = pathinfo($filename, PATHINFO_FILENAME);

        //Check each thumbnail configuration and create thumbnail in Disk
        foreach ($this->thumbnailConfigs as $label => $config) {
            $clone = clone $original;

            //Scale
            $clone->scale($config->width, $config->height);

            //Get Extension
            $extension = $config->format;

            //Get  Encoded Image
            $encoded = ImageHelper::encodeImage($clone,  $extension, $config->quality);

            //Create Temporal File
            $tempStream = tmpfile();
            fwrite($tempStream, (string) $encoded);
            rewind($tempStream);

            //Fix Final filename to the path
            $filename =  "{$pathInfo}-{$label}.{$extension}";
            //Final Path
            $thumbPath =  FileHelper::makePath($filename, $file->folder_id);

            //Write in Disk
            Storage::disk($disk)->writeStream($thumbPath, $tempStream, [
                'visibility' => $file->visibility
            ]);

            fclose($tempStream);
        }
    }

    /**
     * Delete Thumbnails with the main file
     */
    public function deleteThumbnails(File $file): bool
    {

        $disk = $file->disk;

        //Not Image
        if (!$file->isImage()) {
            return Storage::disk($disk)->delete(ltrim($file->path, "/"));
        }

        $filename = $file->filename;
        $pathsToDelete = [];

        //Delete extension
        $pathInfo = pathinfo($filename, PATHINFO_FILENAME);

        //Check Thumbnails
        foreach ((array)$this->thumbnailConfigs as $label => $preset) {

            $extension = $preset->format;
            //Get Path
            //$thumbPath = FileHelper::makePath($filename, $label, $format);


            //Fix Final filename to the path
            $filename =  "{$pathInfo}-{$label}.{$extension}";
            //Final Path
            $thumbPath =  FileHelper::makePath($filename, $file->folder_id);
            //Save Path
            $pathsToDelete[] = $thumbPath;
        }

        // Add main file path
        $pathsToDelete[] = $file->path;

        //Delete all at once
        return Storage::disk($disk)->delete($pathsToDelete);
    }
}
