<?php

namespace Modules\Imedia\Repositories\Cache;

use Modules\Imedia\Repositories\ZoneRepository;
use Imagina\Icore\Repositories\Cache\CoreCacheDecorator;

class CacheZoneDecorator extends CoreCacheDecorator implements ZoneRepository
{
    public function __construct(ZoneRepository $zone)
    {
        parent::__construct();
        $this->entityName = 'imedia.zones';
        $this->repository = $zone;
    }
}
