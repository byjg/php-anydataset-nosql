<?php

namespace ByJG\AnyDataset\NoSql\Cache;

use ByJG\AnyDataset\NoSql\KeyValueInterface;
use ByJG\Cache\Exception\InvalidArgumentException;
use ByJG\Cache\Psr16\BaseCacheEngine;
use DateInterval;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class KeyValueCacheEngine extends BaseCacheEngine
{
    protected KeyValueInterface $keyValue;

    protected LoggerInterface|null $logger = null;

    public function __construct(KeyValueInterface $keyValue, LoggerInterface|null $logger = null)
    {
        $this->keyValue = $keyValue;
        $this->logger = $logger;
        if (is_null($logger)) {
            $this->logger = new NullLogger();
        }
    }

    /**
     * Determines whether an item is present in the cache.
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     * @return bool
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function has(string $key): bool
    {
        $key = $this->getKeyFromContainer($key);
        if ($this->keyValue->has($key)) {
            if ($this->keyValue->has("$key.ttl") && time() >= $this->keyValue->get("$key.ttl")) {
                $this->delete($key);
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $key The object KEY
     * @param mixed $default IGNORED IN MEMCACHED.
     * @return mixed Description
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->has($key)) {
            $key = $this->getKeyFromContainer($key);
            $this->logger->info("[KeyValueInterface] Get '$key' fromCache");
            return unserialize($this->keyValue->get($key));
        } else {
            $this->logger->info("[KeyValueInterface] Not found '$key'");
            return $default;
        }
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string                $key   The key of the item to store.
     * @param mixed                 $value The value of the item to store, must be serializable.
     * @param null|int|DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and
     *                                     the driver supports TTL then the library may set a default value
     *                                     for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool
    {
        $key = $this->getKeyFromContainer($key);

        $this->logger->info("[KeyValueInterface] Set '$key' in Cache");

        $this->keyValue->put($key, serialize($value));
        if (!empty($ttl)) {
            $this->keyValue->put("$key.ttl", $this->addToNow($ttl));
        }

        return true;
    }

    public function clear(): bool
    {
        return false;
    }

    /**
     * Unlock resource
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $key = $this->getKeyFromContainer($key);

        $this->keyValue->remove($key);
        $this->keyValue->remove("$key.ttl");
        return true;
    }

    public function isAvailable(): bool
    {
        return true;
    }
}
