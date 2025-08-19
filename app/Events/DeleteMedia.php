<?php

namespace Modules\Imedia\Events;


class DeleteMedia
{
    public $params;

    public function __construct($params = null)
    {
        $this->params = $params;
    }
}
