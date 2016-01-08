TumbleweedCache
===============

[![Build Status](https://travis-ci.org/jmatosp/TumbleweedCache.svg?branch=master)](https://travis-ci.org/jmatosp/TumbleweedCache) [![Coverage Status](https://coveralls.io/repos/jmatosp/TumbleweedCache/badge.svg?branch=master&service=github)](https://coveralls.io/github/jmatosp/TumbleweedCache?branch=master)

*PHP Caching - PSR-6 implementation*

This library provides Calling Libraries cache services without development.

Implementations for well known cache infrastructure, clever cache using multi-level cache and clustered like cache.

Drivers available: *APCu*, *Redis*, *Files*, *Memcached*, *Memcache*, *Memory*, *2 Level Cache* 

All drivers where tested using PHPUnit and a great [3rd party testing suite for PSR-6](https://github.com/php-cache/integration-tests) 


Install
=======

    composer require jmatosp/tumbleweed-cache

Usage
=====

Simple to use, Tumbleweed Cache will try to use one of the available drivers APCu, Redis or Files 

    $cache = CacheFactory::make();
    $item = $cache->getItem('my_key');
    $item->set('value');
    $item->expiresAfter(60);
    $cache->save($item);
    echo $cache->getItem('my_key')->get();
    // will output "value"
    
or not using CacheFactory (APCu)
    
    $cache = new APCuCache();
    $cache->getItem('hello')->set('value');
    echo $cache->getItem('hello')->get();   // output: "value"

You can specify the cache implementation to use:
 
**APCu**

This driver supports both apc and apcu, works with HHVM (legacy), apcu only PHP7 and apc/apcu on PHP5.6 

    $cache = CacheFactory::make(CacheFactory::APCU);
    $item = $cache->getItem('my_key');
    $item->set('value');
    $cache->save($item);
    echo $cache->getItem('my_key')->get();
    // will output "value"
    
**Redis**

    // if you dont provide a redis connection the factory will try to connect to default port on localhost
    $redis = new Redis();
    $redis->connect('127.0.0.1');
    $cache = CacheFactory::make(CacheFactory::REDIS, $redis);
    $item = $cache->getItem('my_key');
    $item->set('value');
    $cache->save($item);
    echo $cache->getItem('my_key)->get();
    // will output "value"

**Files**

**Memcache**

**Two level**

Two level cache aims to use 2 cache repositories, as failover with two remote caches or a combination of one local to 
with faster access like APCu and one remote typically Redis or Memcached.

Sample using APCu as first level (faster) and Redis second level (fast)

    $localCache = CacheFactory::make(CacheFactory::APCU);
    $remoteCache = CacheFactory::make(CacheFactory::REDIS);
    $megaCache = CacheFactory::make(CacheFactory::TWO_LEVEL, $localCache, $remoteCache);
    $item = $cache->getItem('my_key');
    $item->set('value');
    $cache->save($item);
    echo $cache->getItem('my_key')->get();
    // will output "value"
    

Cache Factory
=============

Cache factory enables creation to different type of cache infrastructure with an easy interface.

It has a builtin auto-discovery that will try to find the fastest one available, to use the auto-discovery 
simply call the factory without parameters:

    $cacheService = CacheFactory::make();
    
Auto-discovery will try to use cache infrastructure by this order: APCu, APC, Redis, Memcached, Files    


PSR-6 Cache Interface
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


Running tests
============

To run all test including integration you need:

Redis - installed locally on standard port 127.0.0.1:6379
APCu - edit your php.ini and add "apc.enable_cli = 1" to enable tests on APCu
Memcached

