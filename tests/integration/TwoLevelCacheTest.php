<?php

use Cache\IntegrationTests\CachePoolTest;
use JPinto\TumbleweedCache\CacheFactory;

class TwoLevelCacheTest extends CachePoolTest
{
    /**
     * Test with common usage, first level APCu, second level Redis
     */
    public function createCachePool()
    {
        return CacheFactory::make(
            CacheFactory::TWO_LEVEL,
            $this->getFirstLevelCache(),
            $this->getSecondLevelCache()
        );
    }

    private function getFirstLevelCache()
    {
        return CacheFactory::make(CacheFactory::APCU);
    }

    private function getSecondLevelCache()
    {
        $redisClient = new Redis();
        $redisClient->connect('127.0.0.1');
        return CacheFactory::make(CacheFactory::REDIS, $redisClient);
    }
}
