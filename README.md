TumbleweedCache
===============

[![Build Status](https://travis-ci.org/jmatosp/TumbleweedCache.svg?branch=master)](https://travis-ci.org/jmatosp/TumbleweedCache) [![Coverage Status](https://coveralls.io/repos/jmatosp/TumbleweedCache/badge.svg?branch=master&service=github)](https://coveralls.io/github/jmatosp/TumbleweedCache?branch=master) [![Latest Stable Version](https://poser.pugx.org/jmatosp/tumbleweed-cache/v/stable)](https://packagist.org/packages/jmatosp/tumbleweed-cache) [![Total Downloads](https://poser.pugx.org/jmatosp/tumbleweed-cache/downloads)](https://packagist.org/packages/jmatosp/tumbleweed-cache) [![Latest Unstable Version](https://poser.pugx.org/jmatosp/tumbleweed-cache/v/unstable)](https://packagist.org/packages/jmatosp/tumbleweed-cache) [![License](https://poser.pugx.org/jmatosp/tumbleweed-cache/license)](https://packagist.org/packages/jmatosp/tumbleweed-cache)

*PHP Caching - PSR-6 implementation*

This library provides Calling Libraries cache services without development.

Implementations for well known cache infrastructure, clever cache using multi-level cache and clustered like cache.

Drivers available: *APCu*, *Redis*, *Files*, *Memcached*, *Memory*, *2 Level Cache* 

All drivers where tested using PHPUnit and a great [3rd party testing suite for PSR-6](https://github.com/php-cache/integration-tests) 


Install
=======

    composer require jmatosp/tumbleweed-cache

Usage
=====

Simple to use, Tumbleweed Cache will try to use one of the available drivers APCu, Redis or Files 

    $cache = CacheFactory::make();
    $item = $cache->getItem('my_key')
                ->set('value')
                ->expiresAfter(60);
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
    $item = $cache->getItem('my_key')->set('value');
    $cache->save($item);
    echo $cache->getItem('my_key')->get();
    // will output "value"
    
**Redis**

    // if you don't provide a redis connection the factory will try to connect to default port on localhost
    $redis = new Redis();
    $redis->connect('127.0.0.1');
    $cache = CacheFactory::make(CacheFactory::REDIS, $redis);
    $item = $cache->getItem('my_key')->set('value');
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
    $item = $cache->getItem('my_key')->set('value');
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

Running tests
============

To run all test including integration you need:

- Redis - installed locally on standard port 127.0.0.1:6379
- APCu - edit your php.ini and add "apc.enable_cli=1" after the extension loding to enable tests on APCu
- Memcached

Optionally you can run on Vagrant:

    vagrant up
    vagrant ssh
    cd /vagrant
    composer install
    vendor/bin/phpunit
