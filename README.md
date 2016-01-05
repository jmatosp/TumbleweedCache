TumbleweedCache - PHP Caching PSR-6 implementation
========

Caching library implementation using PSR-6 Standard.

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

All cache item pool implementations use PSR-6 interfaces, for details please visit [PHPFig PSR-6](http://PHPfiG)

A quick overview of methods available:

