---
sidebar_position: 5
---

# Cache Interface

The class `KeyValueCacheEngine` adds a cache layer on top of any KeyValueStore, implementing the PSR-16 cache interface.

It allows you to cache the results locally and avoid unnecessary calls to the KeyValueStore.

## Basic Usage

```php
<?php
use ByJG\AnyDataset\NoSql\Cache\KeyValueCacheEngine; 
use ByJG\AnyDataset\NoSql\Factory;

// Create the KeyValueStore
Factory::registerDriver(\ByJG\AnyDataset\NoSql\AwsS3Driver::class);
$keyValueStore = Factory::getInstance('s3://...');

// Create the Cache Object
$cache = new KeyValueCacheEngine($keyValueStore);

// Store a value with the default TTL
$cache->set('key', 'value');

// Store with a specific TTL (in seconds)
$cache->set('key', 'value', 3600); // 1 hour

// Or with a DateInterval
$cache->set('key', 'value', new \DateInterval('PT1H')); // 1 hour

// Retrieve a value (returns null if not found or expired)
$value = $cache->get('key');

// Retrieve with a default value if not found
$value = $cache->get('key', 'default value');

// Check if a key exists and is not expired
if ($cache->has('key')) {
    // Key exists and is not expired
}

// Delete a key
$cache->delete('key');
```

## Logging

You can provide a PSR-3 compatible logger to the cache engine:

```php
<?php
use ByJG\AnyDataset\NoSql\Cache\KeyValueCacheEngine;
use Psr\Log\LoggerInterface;

// Create your PSR-3 compatible logger
$logger = new YourPsrLogger();

// Pass it to the cache engine
$cache = new KeyValueCacheEngine($keyValueStore, $logger);

// Now operations will be logged
$cache->set('key', 'value'); // Will log the operation
$value = $cache->get('key'); // Will log the retrieval
```

## Implementation Details

The `KeyValueCacheEngine` stores values in the underlying KeyValueStore with the following structure:

- Values are stored with the cache key as provided
- TTL values are stored with the `.ttl` suffix appended to the key
- Values are serialized before storage
- TTL is stored as a Unix timestamp

Note that the `clear()` method to clear all cache entries is not implemented (returns false) as it would require listing all keys in the KeyValueStore.

## Available Methods

- `get(string $key, mixed $default = null): mixed` - Fetches a value from the cache
- `set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool` - Stores a value in the cache
- `delete(string $key): bool` - Removes a value from the cache
- `has(string $key): bool` - Determines if a cache key exists and is not expired
- `clear(): bool` - Not implemented, always returns false
- `isAvailable(): bool` - Checks if the cache is available, always returns true 