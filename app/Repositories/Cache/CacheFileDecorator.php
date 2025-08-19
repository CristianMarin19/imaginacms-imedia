<?php

namespace Modules\Imedia\Repositories\Cache;

use Modules\Imedia\Repositories\FileRepository;
use Imagina\Icore\Repositories\Cache\CoreCacheDecorator;

class CacheFileDecorator extends CoreCacheDecorator implements FileRepository
{
    public function __construct(FileRepository $file)
    {
        parent::__construct();
        $this->entityName = 'imedia.files';
        $this->repository = $file;
    }
}
