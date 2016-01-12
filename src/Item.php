<?php

namespace JPinto\TumbleweedCache;

use DateTime;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;

class Item implements CacheItemInterface, CacheItemSubjectInterface
{
    /**
     * @var string;
     */
    private $key;

    /**
     * @var mixed
     */
    private $value = null;

    /**
     * @var DateTimeInterface
     */
    private $expiresAt;

    /**
     * @var bool
     */
    private $isHit = false;

    /**
     * @var CacheItemObserverInterface
     */
    private $observer;

    /**
     * Item constructor.
     * @param string $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Returns the key for the current cache item.
     *
     * The key is loaded by the Implementing Library, but should be available to
     * the higher level callers when needed.
     *
     * @return string
     *   The key string for this cache item.
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Retrieves the value of the item from the cache associated with this object's key.
     *
     * The value returned must be identical to the value originally stored by set().
     *
     * If isHit() returns false, this method MUST return null. Note that null
     * is a legitimate cached value, so the isHit() method SHOULD be used to
     * differentiate between "null value was found" and "no value was found."
     *
     * @return mixed
     *   The value corresponding to this cache item's key, or null if not found.
     */
    public function get()
    {
        if ($this->isHit()) {
            return $this->value;
        }

        // expired
        return null;
    }

    /**
     * Confirms if the cache item lookup resulted in a cache hit.
     *
     * Note: This method MUST NOT have a race condition between calling isHit()
     * and calling get().
     *
     * @return bool
     *   True if the request resulted in a cache hit. False otherwise.
     */
    public function isHit()
    {
        if (! $this->isHit) {
            return false;
        }

        if (null === $this->expiresAt) {
            return true;
        }

        $now = new DateTime();

        return $now < $this->expiresAt;
    }

    /**
     * Sets the value represented by this cache item.
     *
     * The $value argument may be any item that can be serialized by PHP,
     * although the method of serialization is left up to the Implementing
     * Library.
     *
     * @param mixed $value
     *   The serializable value to be stored.
     *
     * @return static
     *   The invoked object.
     */
    public function set($value)
    {
        $this->isHit = true;
        $this->value = $value;

        return $this;
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param \DateTimeInterface $expiration
     *   The point in time after which the item MUST be considered expired.
     *   If null is passed explicitly, a default value MAY be used. If none is set,
     *   the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     */
    public function expiresAt($expiration)
    {
        $this->expiresAt = $expiration;

        // notify observers
        $this->notify();

        return $this;
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param int|\DateInterval $time
     *   The period of time from the present after which the item MUST be considered
     *   expired. An integer parameter is understood to be the time in seconds until
     *   expiration. If null is passed explicitly, a default value MAY be used.
     *   If none is set, the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     */
    public function expiresAfter($time)
    {
        if ($time instanceof \DateInterval) {
            $this->expiresAt = (new DateTime())->add($time);
        } elseif (is_numeric($time)) {
            $this->expiresAt = (new DateTime())->add(new \DateInterval('PT' . $time . 'S'));
        } else {
            $this->expiresAt = null;
        }

        return $this;
    }

    /**
     * Checks if a key is valid for APCu cache storage
     *
     * @param $key
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function isValidKey($key)
    {
        $invalid = '{}()/\@:';
        if (is_string($key) && !preg_match('/[' . preg_quote($invalid, '/') . ']/', $key)) {
            return true;
        }

        return false;
    }

    public function attach(CacheItemObserverInterface $observer)
    {
        $this->observer = $observer;
    }

    public function detach(CacheItemObserverInterface $observer)
    {
        $this->observer = null;
    }

    public function notify()
    {
        // no observers
        if (null === $this->observer) {
            return;
        }

        // doesnt expires
        if (null === $this->expiresAt) {
            return;
        }

        $this->observer->update($this, $this->expiresAt);
    }
}
