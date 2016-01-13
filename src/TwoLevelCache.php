<?php

namespace JPinto\TumbleweedCache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class TwoLevelCache implements CacheItemPoolInterface
{
    /**
     * @var CacheItemPoolInterface
     */
    private $local;

    /**
     * @var CacheItemPoolInterface
     */
    private $remote;

    /**
     * LocalRemoteCacheItemPool constructor.
     * @param CacheItemPoolInterface $local
     * @param CacheItemPoolInterface $remote
     */
    public function __construct(CacheItemPoolInterface $local, CacheItemPoolInterface $remote)
    {
        $this->local = $local;
        $this->remote = $remote;
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
        // try to fetch first from local cache
        if ($this->local->hasItem($key)) {
            return $this->local->getItem($key);
        }

        // now on remote
        if ($this->remote->hasItem($key)) {
            return $this->remote->getItem($key);
        }

        // not found anywhere
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
     *
     * @todo performance tune to fetch all keys at once from driver
     */
    public function getItems(array $keys = array())
    {
        $items = [];

        foreach ($keys as $key) {
            $items[$key] = $this->getItem($key);
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
        if ($this->local->hasItem($key) || $this->remote->hasItem($key)) {
            return true;
        }

        return false;
    }

    /**
     * Deletes all items in the pool.
     *
     * @return bool
     *   True if the pool was successfully cleared. False if there was an error.
     */
    public function clear()
    {
        return ($this->local->clear() && $this->remote->clear());
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
        return ($this->local->deleteItem($key) && $this->remote->deleteItem($key));
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
     *
     * @todo performance tune to fetch all keys at once from driver
     */
    public function deleteItems(array $keys)
    {
        $result = true;

        foreach ($keys as $key) {
            $result = $result && $this->deleteItem($key);
        }

        return $result;
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

        return ($this->local->save($item) && $this->remote->save($item));
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
        return ($this->local->saveDeferred($item) && $this->remote->saveDeferred($item));
    }

    /**
     * Persists any deferred cache items.
     *
     * @return bool
     *   True if all not-yet-saved items were successfully saved or there were none. False otherwise.
     */
    public function commit()
    {
        return ($this->local->commit() && $this->remote->commit());
    }
}
