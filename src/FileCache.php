<?php

namespace JPinto\TumbleweedCache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class FileCache implements CacheItemPoolInterface
{
    /**
     * @var CacheItemInterface[]
     */
    private $deferredStack = [];

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

        $file = @file_get_contents($this->filenameFor($key));

        if (false !== $file) {
            return unserialize($file);
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
        $this->assertValidKey($key);

        return isset($this->deferredStack[$key]) || file_exists($this->filenameFor($key));
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

        $result = true;
        foreach (glob($this->getFolder() . '/' . $this->getFilenamePrefix() . '*') as $filename) {
            $result = $result && unlink($filename);
        }

        return $result;
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

        if (isset($this->deferredStack[$key])) {
            unset($this->deferredStack[$key]);
        }

        @unlink($this->filenameFor($key));

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

        $bytes = file_put_contents($this->filenameFor($item->getKey()), serialize($item));

        return (false !== $bytes);
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

    private function filenameFor($key)
    {
        return $this->getFolder() . '/' . $this->getFilenamePrefix() . $key;
    }

    /**
     * Checks if a key is valid for APCu cache storage
     *
     * @param $key
     * @throws InvalidArgumentException
     */
    private function assertValidKey($key)
    {
        $invalid = '{}()/\@:';
        if (is_string($key) && ! preg_match('/['.preg_quote($invalid, '/').']/', $key)) {
            return;
        }

        throw new InvalidArgumentException('invalid key: ' . var_export($key, true));
    }

    /**
     * @return string
     */
    private function getFolder()
    {
        return sys_get_temp_dir();
    }

    /**
     * @return string
     */
    private function getFilenamePrefix()
    {
        return 'tumbleweed-cache-';
    }

    public function __destruct()
    {
        $this->commit();
    }
}
