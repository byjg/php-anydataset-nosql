# CloudFlare KV

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getKeyValueInstance('kv://auth_email:auth_key@account_id/namespace');
```

# List all objects

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getKeyValueInstance('kv://....');
$iterator = $kv->getIterator();
print_r($iterator->toArray());
```

You can add some a prefix to search and a limit to search:

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getKeyValueInstance('kv://....');
$iterator = $kv->getIterator([
    "prefix" => "prefix_to_match",
    "limit" => 30
]);
print_r($iterator->toArray());

// And try to get the next if exists:

$iterator = $kv->getIterator($this->getLastCursor());
print_r($iterator->toArray());
```





# Inserting/Updating data

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getKeyValueInstance('kv://....');
$kv->put("object_name", "value");
```

Put Bulk:

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getKeyValueInstance('kv://....');
$bulk = [
    new \ByJG\AnyDataset\NoSql\KeyValueDocument("key1", "value1"),
    new \ByJG\AnyDataset\NoSql\KeyValueDocument("key2", "value2"),
];
$kv->putBatch($bulk);
```


# Retrieve a value

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getKeyValueInstance('kv://....');
$value = $kv->get("object_name");
```

# Remove a value

```php
<?php
$kv = \ByJG\AnyDataset\NoSql\Factory::getKeyValueInstance('kv://....');
$kv->remove("object_name");
```


