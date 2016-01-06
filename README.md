TumbleweedCache
===============

[![Build Status](https://travis-ci.org/jmatosp/TumbleweedCache.svg?branch=master)](https://travis-ci.org/jmatosp/TumbleweedCache) [![Coverage Status](https://coveralls.io/repos/jmatosp/TumbleweedCache/badge.svg?branch=master&service=github)](https://coveralls.io/github/jmatosp/TumbleweedCache?branch=master)

*PHP Caching PSR-6 implementation*

This library provides Calling Libraries cache services without development and driver agnostic.

Implementations for well known cache infrastructure, clever cache using multi-level cache and clustered like cache.

Drivers available: *APCu*, *Redis*, *Memcached*, *Files*, *Memcache*, *Memory*, *Mocked*, *2 Level Cache*, *Failover* 

All drivers where tested using PHPUnit and a great [3rd party testing suite for PSR-6](https://github.com/php-cache/integration-tests) 


Install
=======

    composer require jmatosp/tumbleweed-cache

Usage
=====

Simple to use, Tumbleweed Cache will try to use one of the available drivers APCu, Redis or Files 

    $cache = CacheItemPoolFactory::make();
    $item = $cache->getItem('my_key');
    $item->set('value');
    $item->expiresAfter(60);
    $cache->save($item);
    echo $cache->getItem('my_key)->get();
    // will output "value"

You can specify the cache implementation to use:
 
**APCu**

    $cache = CacheItemPoolFactory::make(CacheItemPoolFactory::APCU);
    // or
    // $cache = new APCuCacheItemPool();
    $item = $cache->getItem('my_key');
    $item->set('value');
    $cache->save($item);
    echo $cache->getItem('my_key)->get();
    // will output "value"
    
**Redis**

    $redis = new Redis();
    $redis->connect('127.0.0.1');
    $cache = CacheItemPoolFactory::make(CacheItemPoolFactory::REDIS, $redis);
    // or
    // $cache = new RedisCacheItemPool($redis);
    $item = $cache->getItem('my_key');
    $item->set('value');
    $cache->save($item);
    echo $cache->getItem('my_key)->get();
    // will output "value"

**Files**

**Memcache**

**Failover**



**Two level**

Two level cache aims to use 2 cache repositories, one local to instance with faster access like APCu and
one remote share between nodes typically Redis or Memcached.

Sample using APCu as first level (faster) and Redis second level (fast)

    $localCache = CacheItemPoolFactory::make(CacheItemPoolFactory::APCU);
    $redis = new Redis();
    $redis->connect('127.0.0.1');
    $remoteCache = CacheItemPoolFactory::make(CacheItemPoolFactory::REDIS, $redis);
    $megaCache = CacheItemPoolFactory::make(CacheItemPoolFactory::TWO_LEVEL, $localCache, $remoteCache);
    $item = $cache->getItem('my_key');
    $item->set('value');
    $cache->save($item);
    echo $cache->getItem('my_key)->get();
    // will output "value"

Cache Item Pool Interface
=========================

All cache item pool implementations use PSR-6 interfaces, for details please visit [PHP-FIG PSR-6: Caching Interface](http://www.php-fig.org/psr/psr-6/)

A quick overview of methods available:

CacheItemInterface - getKey()
---------------------------

    /***
     * Returns the key for the current cache item.
     *
     * The key is loaded by the Implementing Library, but should be available to
     * the higher level callers when needed.
     *
     * @return string
     *   The key string for this cache item.
     */
    public function getKey()* 

usage:
    
    $cache = CacheItemPoolFactory::make();
    $item = $cache->getItem('my_key');
    $item->set('value');
    $cache->save($item);
    echo $cache->getItem('my_key)->getKey();
    // will output "my_key"


