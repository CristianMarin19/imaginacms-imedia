<?php

namespace Modules\Imedia\Transformers;

use Imagina\Icore\Transformers\CoreResource;

class FileTransformer extends CoreResource
{
    /**
     * Attribute to exclude relations from transformed data
     * @var array
     */
    protected array $excludeRelations = [];

    /**
     * Method to merge values with response
     *
     * @return array
     */
    public function modelAttributes($request): array
    {

        return [
            'isImage' => $this->isImage(),
            'isVideo' => $this->isVideo(),
            'url' => $this->url ?? "#",
            'thumbnails' => $this->thumbnails
        ];
    }
}
