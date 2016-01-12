<?php

namespace JPinto\TumbleweedCache;

use Psr\Cache\CacheItemPoolInterface;
use Redis;

/**
 * Creates a cache pool instance base on user selection or tries to guess the best one
 * @codeCoverageIgnore has partial coverage
 */
class CacheFactory
{
    const MEMORY = 1;
    const APCU = 2;
    const REDIS = 3;
    const TWO_LEVEL = 4;
    const FILE = 5;

    /**
     * @param null $type
     * @param $arg0
     * @param $arg1
     * @throws CacheException
     *
     * @return CacheItemPoolInterface
     */
    public static function make($type = null, $arg0 = null, $arg1 = null)
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
                    throw new CacheException('APCu is not available: not installed');
                }
                return new APCuCache();

            case self::REDIS:
                if (! static::isRedisAvailable() || ! $arg0 instanceof \Redis) {
                    throw new CacheException('Redis cache not available: not installed or argument not a Redis instance');
                }
                return new RedisCache($arg0);

            case self::FILE:
                if (! static::isFilesWritable()) {
                    throw new CacheException('temp dir is not writable');
                }
                return new FileCache();

            case self::TWO_LEVEL:
                if (
                    ! $arg0 instanceof CacheItemPoolInterface ||
                    ! $arg1 instanceof CacheItemPoolInterface
                ) {
                    throw new CacheException('Two level cache needs two arguments of CacheItemPoolInterface');
                }
                return new TwoLevelCache($arg0, $arg1);

            default:
                throw new CacheException('invalid user cache pool type');
        }
    }

    /**
     * @return CacheItemPoolInterface
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

        if (static::isFilesWritable()) {
            return new FileCache();
        }

        throw new CacheException('could not find a suitable cache pool');
    }

    private static function isFilesWritable()
    {
        return is_writable(sys_get_temp_dir());
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
        return (function_exists('apcu_fetch') || function_exists('apc_fetch'));
    }
}
