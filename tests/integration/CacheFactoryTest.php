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

    public function testFileCacgeFactory()
    {
        $cache = CacheFactory::make(CacheFactory::FILE);
        $this->assertInstanceOf('Psr\Cache\CacheItemPoolInterface', $cache);
    }

//    /**
//     * @expectedException \Psr\Cache\CacheException
//     */
//    public function testAPCuNotAvailable()
//    {
//        runkit_function_rename('apcu_fetch', 'copy_apcu_fetch');
//        runkit_function_rename('apc_fetch', 'copy_apc_fetch');
//
//        $cache = CacheFactory::make(CacheFactory::APCU);
//
//        runkit_function_rename('copy_apcu_fetch', 'apcu_fetch');
//        runkit_function_rename('copy_apc_fetch', 'apc_fetch');
//    }
}