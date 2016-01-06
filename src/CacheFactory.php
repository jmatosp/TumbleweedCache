<?php

namespace JPinto\TumbleweedCache;

use Psr\Cache\CacheItemPoolInterface;
use Redis;

/**
 * Creates a cache pool instance base on user selection or tries to guess the best one
 */
class CacheFactory
{
    const MEMORY = 1;
    const APCU = 2;
    const REDIS = 3;
    const TWO_LEVEL = 4;

    /**
     * @param null $type
     * @param array ...$args
     * @return CacheItemPoolInterface
     * @throws CacheException
     */
    public static function make($type = null, ...$args)
    {
        if (null === $type) {
            return static::autoDiscovery();
        }

        // user selection
        switch ($type) {
            case self::MEMORY:
                return new MemoryCache();

            case self::APCU:
                if (! static::isAPCuAvailable()) {
                    throw new CacheException('APCu is not available: not installed or running on CLI');
                }
                return new APCuCache();

            case self::REDIS:
                if (! static::isRedisAvailable() || ! $args[0] instanceof \Redis) {
                    throw new CacheException('Redis cache not available: not installed or argument not a Redis instance');
                }
                return new RedisCache($args[0]);

            case self::TWO_LEVEL:
                if (
                    ! $args[0] instanceof CacheItemPoolInterface ||
                    ! $args[1] instanceof CacheItemPoolInterface
                ) {
                    throw new CacheException('Two level cache needs two arguments of CacheItemPoolInterface');
                }
                return new TwoLevelCache($args[0], $args[1]);

            default:
                throw new CacheException('invalid user cache pool type');
        }
    }

    /**
     * @return CacheItemPoolInterface
     *
     * @throws CacheException
     */
    private static function autoDiscovery()
    {
        // use APCu first if available
        if (static::isAPCuAvailable()) {
            return new APCuCache();
        }

        // is redis installed and available in localhost standard port?
        if (static::isRedisAvailable()) {
            $redis = new Redis();
            if ($redis->connect('127.0.0.1', 6379)) {
                return new RedisCache($redis);
            };
        }

        throw new CacheException('could not find a suitable cache pool');
    }

    /**
     * @return bool
     */
    private static function isRedisAvailable()
    {
        return class_exists('Redis');
    }

    /**
     * @return bool
     */
    private static function isAPCuAvailable()
    {
        return (function_exists('apc_fetch'));
    }
}
