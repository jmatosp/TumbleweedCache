<?php

use Cache\IntegrationTests\CachePoolTest;
use JPinto\TumbleweedCache\APCuCache;

class APCuCacheTest extends CachePoolTest
{
    public function createCachePool()
    {
        return new APCuCache();
    }
}