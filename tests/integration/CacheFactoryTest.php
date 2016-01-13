<?php

use JPinto\TumbleweedCache\CacheFactory;

class CacheFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testAutoDiscovery()
    {
        $cache = CacheFactory::make();
        $this->assertInstanceOf('Psr\Cache\CacheItemPoolInterface', $cache);
    }

    /**
     * @expectedException \Psr\Cache\CacheException
     */
    public function testInvalidCacheType()
    {
        $cache = CacheFactory::make('foo');
    }

    /**
     * @expectedException \Psr\Cache\CacheException
     */
    public function testInvalidInstancesToTwoLevel()
    {
        $cache = CacheFactory::make(CacheFactory::TWO_LEVEL, 'this is not a Redis instance', 'this is not a Redis instance');
    }


    /**
     * @expectedException \Psr\Cache\CacheException
     */
    public function testInvalidInstancesToRedisCache()
    {
        $cache = CacheFactory::make(CacheFactory::REDIS, 'this is not a Redis instance');
    }

    public function testFileCacheFactory()
    {
        $cache = CacheFactory::make(CacheFactory::FILE);
        $this->assertInstanceOf('Psr\Cache\CacheItemPoolInterface', $cache);
    }

    public function testTwoLevelCacheSaveAndFetch()
    {
        $memory1 = CacheFactory::make(CacheFactory::MEMORY);
        $memory2 = CacheFactory::make(CacheFactory::MEMORY);

        $cache = CacheFactory::make(CacheFactory::TWO_LEVEL, $memory1, $memory2);

        $item = $cache->getItem('key')->set('value');
        $cache->save($item);

        // assert that both caches have the item
        $this->assertTrue($memory1->getItem('key')->isHit());
        $this->assertTrue($memory2->getItem('key')->isHit());

        // hack and delete on first cache
        $memory1->clear();

        $this->assertTrue($memory2->getItem('key')->isHit());
    }

    public function testMemcachedDeleteMultiWorksOnHHVM()
    {
        $memcached = new Memcached('hello');
        $memcached->addServer('localhost', 11211);

        $cache = CacheFactory::make(CacheFactory::MEMCACHED, $memcached);

        // emulate HHVM environment
        define('HHVM_VERSION', 'cat');

        $item = $cache->getITem('key')->set('value');
        $cache->save($item);

        // should delete multi anyway
        $cache->deleteItems(['key']);
        $this->assertFalse($cache->getItem('key')->isHit());

    }
}
