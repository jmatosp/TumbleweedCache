<?php

use Cache\IntegrationTests\CachePoolTest;
use JPinto\TumbleweedCache\CacheFactory;

class APCuCacheTest extends CachePoolTest
{
    public function createCachePool()
    {
        return CacheFactory::make(CacheFactory::APCU);
    }
}