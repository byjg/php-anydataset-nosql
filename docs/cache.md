---
sidebar_position: 5
---

# Cache Interface

The class `KeyValueCacheEngine` adds a cache layer on top of the KeyValueStore.

It allows you to cache the results locally and avoid unnecessary calls to the KeyValueStore.

This package provides a PSR-16 cache implementation for the Key-Value store.

To use as a cache store you just need to:

```php
<?php
use ByJG\AnyDataset\NoSql\Cache\KeyValueCacheEngine; 
use ByJG\AnyDataset\NoSql\Factory;

// Create the KeyValueStore
Factory::registerDriver(\ByJG\AnyDataset\NoSql\AwsS3Driver::class);
$keyValueStore = Factory::getInstance('s3://...');

// Create the Cache Object
$cache = new KeyValueCacheEngine($keyValueStore);
$cache->set('key', 'value');
echo $cache->get('key');
``` 