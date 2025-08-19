<?php

namespace Modules\Imedia\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Imedia\Models\File;

class FilesRelation
{
  public function resolve(Model $model): MorphToMany
  {
    return $model->morphToMany(File::class, 'imageable', 'imedia__imageables')
      ->with('translations')
      ->withPivot('zone', 'id')
      ->withTimestamps()
      ->orderBy('order');
  }
}
