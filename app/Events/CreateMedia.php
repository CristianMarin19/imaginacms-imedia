<?php

namespace Modules\Imedia\Events;


class CreateMedia
{
    public $params;

    public function __construct($params = null)
    {
        $this->params = $params;
    }
}
