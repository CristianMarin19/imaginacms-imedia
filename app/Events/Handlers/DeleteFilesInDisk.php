<?php

namespace Modules\Imedia\Events\Handlers;

use Illuminate\Support\Facades\Storage;

class DeleteFilesInDisk
{


    public function handle($event)
    {

        // All params Event
        $params = $event->params;
        // Extra data custom event entity
        //$extraData = $params['extraData'];

        $model = $params['model'];

        //Validation Folder
        if ($model->is_folder) {
            Storage::disk($model->disk)->deleteDirectory($model->path);
        }

        //Delete File (Main File) and Delete Thumbnails if is an Image
        $delete = app('Modules\Imedia\Services\ThumbnailService')->deleteThumbnails($model);
    }
}
