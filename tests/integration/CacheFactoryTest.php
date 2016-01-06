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
}