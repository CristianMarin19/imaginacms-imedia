<?php

namespace Modules\Imedia\Http\Controllers\Api;

use Imagina\Icore\Http\Controllers\CoreApiController;
//Model
use Modules\Imedia\Models\Zone;
use Modules\Imedia\Repositories\ZoneRepository;

class ZoneApiController extends CoreApiController
{
  public function __construct(Zone $model, ZoneRepository $modelRepository)
  {
    parent::__construct($model, $modelRepository);
  }
}
