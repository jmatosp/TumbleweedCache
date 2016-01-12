<?php

namespace JPinto\TumbleweedCache;

use DateTimeInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Driver for APCu Cache
 * uses apcu_* functions witch are supported by PHP5.6 and PHP7 but not by HHVM.
 * apc_* function are supported by PHP5.6 and HHVM but not PHP7
 */
class APCuCache implements CacheItemPoolInterface, CacheItemObserverInterface
{
    /**
     * @var CacheItemInterface[]
     */
    private $deferredStack = [];

    /**
     * are we on a HHVM
     * @var bool
     */
    private $legacy = false;

    /**
     * @var CacheItemSubjectInterface
     */
    private $itemsObserving;

    /**
     * APCuCache constructor.
     */
    public function __construct()
    {
        $this->legacy = ini_get('apc.enabled') && function_exists('apc_store');
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
        $this->assertValidKey($key);

        if (isset($this->deferredStack[$key])) {
            return clone $this->deferredStack[$key];
        }

        if ($this->legacy) {
            $item = apc_fetch($key);
        } else {
            $item = apcu_fetch($key);
        }
        if (false !== $item) {
            /** @var Item $item */
            $item = unserialize($item);

            // observe this item
            $item->attach($this);

            return $item;
        }

        // new Item
        $item = new Item($key);

        // observe this item
        $item->attach($this);

        return $item;
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
            $this->assertValidKey($key);
        }

        if ($this->legacy) {
            $values = apc_fetch($keys);
        } else {
            $values = apcu_fetch($keys);
        }

        /** @var Item[] $items */
        $items = [];
        if (false !== $values) {
            foreach ($keys as $key) {
                $items[$key] = isset($values[$key]) ? unserialize($values[$key]) : new Item($key);
                // observe this item
                $items[$key]->attach($this);
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
        $this->assertValidKey($key);

        if ($this->legacy) {
            $exists = apc_exists($key);
        } else {
            $exists = apcu_exists($key);
        }

        return $this->isItemInDeferred($key) || $exists;
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

        $this->itemsObserving = [];

        if ($this->legacy) {
            return apc_clear_cache();
        } else {
            return apcu_clear_cache();
        }
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
        $this->assertValidKey($key);

        if (null !== $this->itemsObserving) {
            foreach ($this->itemsObserving as $subjectKey =>  $subjects) {
                /** @var Item $item */
                $item = $subjects['item'];
                if ($item->getKey() === $key) {
                    unset($this->itemsObserving[$subjectKey]);
                }
            }
        }

        if (isset($this->deferredStack[$key])) {
            unset($this->deferredStack[$key]);
        }

        if ($this->legacy) {
            apc_delete($key);
        } else {
            apcu_delete($key);
        }

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
        if (! $item->isHit()) {
            return false;
        }
        
        if ($this->legacy) {
            $store = apc_store($item->getKey(), serialize($item));
        } else {
            $store = apcu_store($item->getKey(), serialize($item));
        }

        return $store;
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
        $result = true;
        
        foreach ($this->deferredStack as $key => $item) {
            $result = $result && $this->save($item);
            unset($this->deferredStack[$key]);
        }

        return $result;
    }

    /**
     * Checks if a key is valid for APCu cache storage
     *
     * @param $key
     * @throws InvalidArgumentException
     */
    private function assertValidKey($key)
    {
        if ( ! Item::isValidKey($key)) {
            throw new InvalidArgumentException('invalid key: ' . var_export($key, true));
        }
    }

    public function __destruct()
    {
        $this->commit();
    }

    /**
     * @param $key
     * @return bool
     */
    private function isItemInDeferred($key)
    {
        // is in stack and not expired
        return isset($this->deferredStack[$key]) && $this->deferredStack[$key]->isHit();
    }

    /**
     * We get notified whenever a Item gets a setExpiredAt of setExpiredAfter
     * @param CacheItemSubjectInterface $cacheItemSubject
     * @param DateTimeInterface $expiresAt
     */
    public function update(CacheItemSubjectInterface $cacheItemSubject, DateTimeInterface $expiresAt)
    {
        $this->itemsObserving[] = ['item' => $cacheItemSubject, 'expiresAt' => $expiresAt];

        // garbage collector
        $this->vacuumItemsObserving();
    }

    /**
     * Prevent memory leaks
     */
    private function vacuumItemsObserving()
    {
        if (null === $this->itemsObserving) {
            return;
        }

        $now = new \DateTime();
        foreach ($this->itemsObserving as $key => $item) {
            if ($now < $item['expiresAt']) {
                unset($this->itemsObserving[$key]);
            }
        }
    }
}
