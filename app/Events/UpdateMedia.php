<?php

namespace Modules\Imedia\Events;


class UpdateMedia
{
    public $params;

    public function __construct($params = null)
    {
        $this->params = $params;
    }
}
