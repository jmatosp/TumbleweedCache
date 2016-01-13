<?php

namespace JPinto\TumbleweedCache;

use Memcached;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class MemcachedCache implements CacheItemPoolInterface
{
    /**
     * @var CacheItemInterface[]
     */
    private $deferredStack = [];

    /**
     * @var Memcached
     */
    private $cacheClient;

    /**
     * MemcachedCache constructor.
     * @param Memcached $memcached
     */
    public function __construct(Memcached $memcached)
    {
        $this->cacheClient = $memcached;
    }

    /**
     * Returns a Cache Item representing the specified key.
     *
     * This method must always return a CacheItemInterface object, even in case of
     * a cache miss. It MUST NOT return null.
     *
     * @param string $key
     *   The key for which to return the corresponding Cache Item.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return CacheItemInterface
     *   The corresponding Cache Item.
     */
    public function getItem($key)
    {
        if (! Item::isValidKey($key)) {
            throw new InvalidArgumentException('invalid key: ' . var_export($key, true));
        }

        if (isset($this->deferredStack[$key])) {
            return clone $this->deferredStack[$key];
        }

        $item = $this->cacheClient->get($key);
        if (false !== $item) {
            return unserialize($item);
        }

        return new Item($key);
    }

    /**
     * Returns a traversable set of cache items.
     *
     * @param array $keys
     * An indexed array of keys of items to retrieve.
     *
     * @throws InvalidArgumentException
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return array|\Traversable
     *   A traversable collection of Cache Items keyed by the cache keys of
     *   each item. A Cache item will be returned for each key, even if that
     *   key is not found. However, if no keys are specified then an empty
     *   traversable MUST be returned instead.
     */
    public function getItems(array $keys = array())
    {
        foreach ($keys as $key) {
            if (! Item::isValidKey($key)) {
                throw new InvalidArgumentException('invalid key: ' . var_export($key, true));
            }
        }

        $values = $this->cacheClient->getMulti($keys);

        /** @var Item[] $items */
        $items = [];
        if (false !== $values) {
            foreach ($keys as $key) {
                $items[$key] = isset($values[$key]) ? unserialize($values[$key]) : new Item($key);
            }
        }

        return $items;
    }

    /**
     * Confirms if the cache contains specified cache item.
     *
     * Note: This method MAY avoid retrieving the cached value for performance reasons.
     * This could result in a race condition with CacheItemInterface::get(). To avoid
     * such situation use CacheItemInterface::isHit() instead.
     *
     * @param string $key
     *    The key for which to check existence.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *  True if item exists in the cache, false otherwise.
     */
    public function hasItem($key)
    {
        if (! Item::isValidKey($key)) {
            throw new InvalidArgumentException('invalid key: ' . var_export($key, true));
        }

        $item = $this->getItem($key);

        return (isset($this->deferredStack[$key]) && $this->deferredStack[$key]->isHit()) || $item->isHit();
    }

    /**
     * Deletes all items in the pool.
     *
     * @return bool
     *   True if the pool was successfully cleared. False if there was an error.
     */
    public function clear()
    {
        $this->deferredStack = [];

        return $this->cacheClient->flush();
    }

    /**
     * Removes the item from the pool.
     *
     * @param string $key
     *   The key for which to delete
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if the item was successfully removed. False if there was an error.
     */
    public function deleteItem($key)
    {
        if ( ! Item::isValidKey($key)) {
            throw new InvalidArgumentException('invalid key: ' . var_export($key, true));
        }

        $this->deleteDeferred($key);

        $this->cacheClient->delete($key);

        return true;
    }

    /**
     * Removes multiple items from the pool.
     *
     * @param array $keys
     *   An array of keys that should be removed from the pool.
     * @throws InvalidArgumentException
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if the items were successfully removed. False if there was an error.
     */
    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            if (! Item::isValidKey($key)) {
                throw new InvalidArgumentException('invalid key: ' . var_export($key, true));
            }

            $this->deleteDeferred($key);
        }

        // HHVM memcached doesn't have this method
        if (defined('HHVM_VERSION')) {
            foreach ($keys as $key) {
                $this->deleteItem($key);
            }
        } else {
            $this->cacheClient->deleteMulti($keys);
        }

        return true;
    }

    /**
     * Persists a cache item immediately.
     *
     * @param CacheItemInterface $item
     *   The cache item to save.
     *
     * @return bool
     *   True if the item was successfully persisted. False if there was an error.
     */
    public function save(CacheItemInterface $item)
    {
        if ( ! $item->isHit()) {
            return false;
        }

        return $this->cacheClient->add($item->getKey(), serialize($item));
    }

    /**
     * Sets a cache item to be persisted later.
     *
     * @param CacheItemInterface $item
     *   The cache item to save.
     *
     * @return bool
     *   False if the item could not be queued or if a commit was attempted and failed. True otherwise.
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        $this->deferredStack[$item->getKey()] = $item;

        return true;
    }

    /**
     * Persists any deferred cache items.
     *
     * @return bool
     *   True if all not-yet-saved items were successfully saved or there were none. False otherwise.
     */
    public function commit()
    {
        $multi = [];
        foreach ($this->deferredStack as $key => $item) {
            $multi[$key] = serialize($this->deferredStack[$key]);
            $this->deleteDeferred($key);
        }

        return $this->cacheClient->setMulti($multi);
    }

    /**
     * @param $key
     */
    private function deleteDeferred($key)
    {
        if (isset($this->deferredStack[$key])) {
            unset($this->deferredStack[$key]);
        }
    }

    public function __destruct()
    {
        $this->commit();
    }
}
