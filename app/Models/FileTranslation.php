<?php

namespace Modules\Imedia\Models;

use Illuminate\Database\Eloquent\Model;

class FileTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'alt',
        'keywords'
    ];
    protected $table = 'imedia__file_translations';
}
