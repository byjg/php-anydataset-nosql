---
sidebar_position: 4
---

# CloudFlare KV

CloudFlare KV provides a key-value storage solution through the CloudFlare API.

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getInstance('kv://auth_email:auth_key@account_id/namespace');
```

## List all objects

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

## Check if a key exists

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getInstance('kv://....');
if ($kv->has("object_name")) {
    // The key exists
}
```

## Inserting/Updating data

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

## Retrieve a value

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getInstance('kv://....');
$value = $kv->get("object_name");
```

## Get a portion of a value

For large objects, you can retrieve just a portion:

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getInstance('kv://....');
// Get 1024 bytes starting from offset 0
$chunk = $kv->getChunk("object_name", [], 1024, 0);
```

## Remove a value

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

## Rename a key

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getInstance('kv://....');
$kv->rename("old_key_name", "new_key_name");
```

----
[Open source ByJG](http://opensource.byjg.com)