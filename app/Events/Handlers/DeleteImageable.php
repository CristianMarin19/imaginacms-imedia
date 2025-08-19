<?php

namespace Modules\Imedia\Events\Handlers;



class DeleteImageable
{

    public function handle($event)
    {

        // All params Event
        $params = $event->params;

        // Get specific data
        $dataFromRequest = $params['data'];
        $model = $params['model'];

        //Delete imageables to disk model
        /* \DB::table('imedia__imageables')->where('imageable_id', $model->id)
            ->where('imageable_type', get_class($model))->delete(); */

        //Delete imageables to disk model
        $model->files()->detach();
    }
}
