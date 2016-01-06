<?php

use Cache\IntegrationTests\CachePoolTest;
use JPinto\TumbleweedCache\RedisCache;

class RedisCacheTest extends CachePoolTest
{
    public function createCachePool()
    {
        $redisClient = new Redis();
        $redisClient->connect('127.0.0.1');
        return new RedisCache($redisClient);
    }
}
