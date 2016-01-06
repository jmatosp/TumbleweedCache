<?php

use Cache\IntegrationTests\CachePoolTest;
use JPinto\TumbleweedCache\CacheItemPoolFactory;
use JPinto\TumbleweedCache\MemoryCache;

class MemoryCacheTest extends CachePoolTest
{
    private $memoryCache = null;

    public function createCachePool()
    {
        // we need this, as memory storage goes way when the instances looses scope
        if (null === $this->memoryCache) {
            $this->memoryCache = CacheItemPoolFactory::make(CacheItemPoolFactory::MEMORY);
        }

        return $this->memoryCache;
    }
}
