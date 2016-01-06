TumbleweedCache
===============

*PHP Caching PSR-6 implementation*

This library provides Calling Libraries cache services without development and driver agnostic.

Implementations for well known cache infrastructure, clever cache using multi-level cache and clustered like cache.

Drivers available: *APCu*, *Redis*, *Memcached*, *Files*, *Memcache*, *Memory*, *Mocked*, *2 Level Cache*, *Clustered* 


Install
=======

    composer require jmatosp/TumbleweedCache

Usage
=====

Simple to use, will try to use one of the available drivers APCu, Redis or Files 

    $cache = CacheItemPoolFactory::make();
    $cache->save(new Item('my_key', 'value', 60));
    echo $cache->getItem('my_key);

You can specify the cache implementation to use:
 
**APCu**

    $cache = CacheItemPoolFactory::make(CacheItemPoolFactory::APCU);
    $cache->save(new Item('my_key', 'value', 60));
    echo $cache->getItem('my_key);
    
**REDIS**

    $cache = CacheItemPoolFactory::make(CacheItemPoolFactory::REDIS, new Redis());
    $cache->save(new Item('my_key', 'value', 60));
    echo $cache->getItem('my_key);

**Files**

**Memcache**

**Clustered**

**Two level**

Sample using APCu as first level (faster) and Redis second level (fast)

    $localCache = CacheItemPoolFactory::make(CacheItemPoolFactory::APCU);
    $remoteCache = CacheItemPoolFactory::make(CacheItemPoolFactory::REDIS, new Redis());
    $megaCache = CacheItemPoolFactory::make(CacheItemPoolFactory::TWO_LEVEL, $localCache, $remoteCache);
    $cache->save(new Item('my_key', 'value', 60));
    echo $cache->getItem('my_key);

Cache Methods Available
=======================

All cache item pool implementations use PSR-6 interfaces, for details please visit [PHPFig PSR-6](http://www.php-fig.org/psr/psr-6/)

A quick overview of methods available:

CacheItemInterface
------------------

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
    $cache->save(new Item('my_key', 'value', 60));
    echo $cache->getItem('my_key)->getKey();
    // will output "my_key"


