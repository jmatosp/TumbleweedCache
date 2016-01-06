<?php

use Cache\IntegrationTests\CachePoolTest;
use JPinto\TumbleweedCache\MemoryCacheItemPool;

class PoolIntegrationTest extends CachePoolTest
{
    private $memoryCache = null;

    public function createCachePool()
    {
        // we need this, as memory storage goes way when the instances looses scope
        if (null === $this->memoryCache) {
            $this->memoryCache = new MemoryCacheItemPool();
        }

        return $this->memoryCache;
    }
}