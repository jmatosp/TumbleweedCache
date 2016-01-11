<?php

use Cache\IntegrationTests\CachePoolTest;
use JPinto\TumbleweedCache\CacheFactory;

class FileCacheTest extends CachePoolTest
{
    public function createCachePool()
    {
        return CacheFactory::make(CacheFactory::FILE);
    }
}