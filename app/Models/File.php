<?php

namespace Modules\Imedia\Models;

use Astrotomic\Translatable\Translatable;
use Imagina\Icore\Models\CoreModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Modules\Imedia\Support\FileHelper;
use Modules\Imedia\Support\FileCollection;

class File extends CoreModel
{
  use Translatable;

  protected $table = 'imedia__files';
  public string $transformer = 'Modules\Imedia\Transformers\FileTransformer';
  public string $repository = 'Modules\Imedia\Repositories\FileRepository';
  public array $requestValidation = [
    'create' => 'Modules\Imedia\Http\Requests\CreateFileRequest',
    'update' => 'Modules\Imedia\Http\Requests\UpdateFileRequest',
  ];
  //Instance external/internal events to dispatch with extraData
  public array $dispatchesEventsWithBindings = [
    //eg. ['path' => 'path/module/event', 'extraData' => [/*...optional*/]]
    'created' => [],
    'creating' => [],
    'updated' => [],
    'updating' => [],
    'deleting' => [
      ['path' => 'Modules\Imedia\Events\FileIsDeleting']
    ],
    'deleted' => []
  ];
  public array $translatedAttributes = [
    'alt',
    'keywords'
  ];
  protected $fillable = [
    'is_folder',
    'filename',
    'path',
    'extension',
    'mimetype',
    'filesize',
    'folder_id',
    'has_watermark',
    'has_thumbnails',
    'disk',
    'visibility'
  ];

  public function url(): Attribute
  {
    //TODO: Deberiamos no calcular la url cada vez... en su lugar guardar la url en DB
    return Attribute::get(function () {
      // If no disk defined → fallback to public asset path
      if (empty($this->disk)) {
        return asset($this->path);
      }
      $disk = Storage::disk($this->disk);
      if ($this->visibility === 'public') {
        return $disk->url($this->path);
      }

      //Case Private |TODO: Pasar los minutos a un setting
      return $disk->temporaryUrl($this->path, now()->addMinutes(5));
    });
  }

  public function thumbnails(): Attribute
  {
    //TODO: Deberiamos no calcular los thubnails cada vez... en su lugar guardarlos en DB
    return Attribute::get(function () {

      //Validation Not Image
      if (!$this->isImage()) return null;

      //Get Attributes
      $thumbs = json_decode(config('imedia.defaultThumbnails'));
      $filename = $this->filename;
      $visibility = $this->visibility;

      //Get Disk
      $disk = Storage::disk($this->disk);

      //Expiration to Private Files
      $expiration = Carbon::now()->addMinutes(5); //TODO OJO

      //Delete extension
      $pathInfo = pathinfo($filename, PATHINFO_FILENAME);

      return collect($thumbs)->mapWithKeys(function ($data, $label) use ($disk, $filename, $pathInfo, $visibility, $expiration) {


        //TODO | Case: External
        /* if (!in_array($this->disk, array_keys(config("filesystems.disks"))))
            return app("Modules\Media\Services\\" . ucfirst($this->disk) . "Service")->getThumbnail($this, $label); */


        //Validate the attribute has_thumbnail
        if (!$this->has_thumbnails)
          return [$label => $this->url];

        //Fix Final filename to the path
        $filename = "{$pathInfo}-{$label}.{$data->format}";
        //Final Path
        $thumbPath = FileHelper::makePath($filename, $this->folder_id);

        //check visibility to get the URL
        if ($visibility == "public")
          $url = $disk->url($thumbPath);
        else
          $url = $disk->temporaryUrl($thumbPath, $expiration);

        return [$label => $url];
      })->toArray();
    });
  }

  /**
   * METHODS
   */
  public function isImage()
  {
    //TODO - EL Setting no funca
    //$imageExtensions = (array)json_decode(setting('imedia::allowedImageTypes', null, config("imedia.allowedImageTypes")));
    $imageExtensions = (array)json_decode(config("imedia.allowedImageTypes"));
    //dd(setting('imedia::allowedImageTypes'));
    // Case external disk
    if (isset($this->disk) && !in_array($this->disk, array_keys(config("filesystems.disks")))) {
      $dataExternalImg = app("Modules\Media\Services\\" . ucfirst($this->disk) . "Service")->getDataFromUrl($this->path);
      return in_array($dataExternalImg['extension'], $imageExtensions);
    } else {
      return in_array(pathinfo($this->path, PATHINFO_EXTENSION), $imageExtensions);
    }
  }

  public function isVideo()
  {
    return str_starts_with($this->mimetype, 'video/');
  }

  /**
   * Implementation to Relations in transformers in Modules.
   */
  public function newCollection(array $models = []): FileCollection
  {
    return new FileCollection($models);
  }
}
