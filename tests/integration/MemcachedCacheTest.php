<?php

use Cache\IntegrationTests\CachePoolTest;
use JPinto\TumbleweedCache\CacheFactory;

class MemcachedCacheTest extends CachePoolTest
{
    public function createCachePool()
    {
        $memcached = new Memcached('cant');
        $memcached->addServer('localhost', 11211);
        return CacheFactory::make(CacheFactory::MEMCACHED, $memcached);
    }
}