---
sidebar_position: 4
title: Cloudflare KV
description: Cloudflare Workers KV storage driver
---

# Cloudflare KV

Cloudflare KV provides a global, low-latency key-value storage solution accessible through the Cloudflare API.

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getInstance('kv://auth_email:auth_key@account_id/namespace');
```

## Basic Operations

### List all objects

You can list all objects in the namespace:

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getInstance('kv://....');
$iterator = $kv->getIterator();
print_r($iterator->toArray());
```

You can add a prefix to filter results and a limit to control the number of items returned:

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getInstance('kv://....');
$iterator = $kv->getIterator([
    "prefix" => "prefix_to_match",
    "limit" => 30
]);
print_r($iterator->toArray());

// To get the next page if it exists:
$iterator = $kv->getIterator($kv->getLastCursor());
print_r($iterator->toArray());
```

:::tip Pagination
Use `getLastCursor()` to retrieve the cursor for the next page of results when working with large datasets.
:::

### Inserting/Updating data

Put a single value:

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getInstance('kv://....');
$kv->put("object_name", "value");
```

Put multiple values in batch:

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getInstance('kv://....');
$bulk = [
    new \ByJG\AnyDataset\NoSql\KeyValueDocument("key1", "value1"),
    new \ByJG\AnyDataset\NoSql\KeyValueDocument("key2", "value2"),
];
$kv->putBatch($bulk);
```

### Retrieve a value

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getInstance('kv://....');
$value = $kv->get("object_name");
```

### Remove a value

Remove a single key:

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getInstance('kv://....');
$kv->remove("object_name");
```

Remove multiple keys at once:

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getInstance('kv://....');
$kv->removeBatch(["key1", "key2"]);
```

:::warning Not implemented
`has()`, `getChunk()` and `rename()` are not implemented by the Cloudflare KV driver and throw
`ByJG\AnyDataset\Core\Exception\NotImplementedException`. Because `has()` is not available, this driver
cannot be used with the [Cache Store](cache.md).
:::

----
[Open source ByJG](http://opensource.byjg.com)