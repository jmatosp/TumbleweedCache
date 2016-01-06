<?php

use Cache\IntegrationTests\CachePoolTest;
use JPinto\TumbleweedCache\CacheFactory;

class RedisCacheTest extends CachePoolTest
{
    public function createCachePool()
    {
        $redisClient = new Redis();
        $redisClient->connect('127.0.0.1');
        return CacheFactory::make(CacheFactory::REDIS, $redisClient);
    }
}
