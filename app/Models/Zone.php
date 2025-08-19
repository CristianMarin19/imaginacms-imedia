<?php

namespace Modules\Imedia\Models;

use Imagina\Icore\Models\CoreModel;

class Zone extends CoreModel
{

    protected $table = 'imedia__zones';
    public string $transformer = 'Modules\Imedia\Transformers\ZoneTransformer';
    public string $repository = 'Modules\Imedia\Repositories\ZoneRepository';
    public array $requestValidation = [
        'create' => 'Modules\Imedia\Http\Requests\CreateZoneRequest',
        'update' => 'Modules\Imedia\Http\Requests\UpdateZoneRequest',
    ];
    //Instance external/internal events to dispatch with extraData
    public array $dispatchesEventsWithBindings = [
        //eg. ['path' => 'path/module/event', 'extraData' => [/*...optional*/]]
        'created' => [],
        'creating' => [],
        'updated' => [],
        'updating' => [],
        'deleting' => [],
        'deleted' => []
    ];

    protected $fillable = [
        'system_name',
        'entity_type',
        'entity_id',
        'options'
    ];

    protected function casts(): array
    {
        return [
            'options' => 'json'
        ];
    }
}
