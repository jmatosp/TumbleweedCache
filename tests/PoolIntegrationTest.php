<?php

use Cache\IntegrationTests\CachePoolTest;

class PoolIntegrationTest extends CachePoolTest
{
    public function createCachePool()
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1');
        return new \JPinto\TumbleweedCache\MemoryCacheItemPool($redis);
    }
}