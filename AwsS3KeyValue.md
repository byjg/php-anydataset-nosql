# AWS S3

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getKeyValueInstance('s3://access_key:secret_key@region/bucket');
```

The full connection string can be:

```
s3://AKA12345678899:aaaaaaaaaaaaaaaaaaaaaaaaa@us-east-1/mybucket
```
# List all objects

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getKeyValueInstance('s3://....');
$iterator = $s3->getIterator();
print_r($iterator->toArray());
```

# Inserting/Updating data

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getKeyValueInstance('s3://....');
$s3->put("object_name", "value");
```

# Retrieve a value

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getKeyValueInstance('s3://....');
$value = $s3->get("object_name");
```

# Remove a value

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getKeyValueInstance('s3://....');
$s3->remove("object_name");
```

# Get parts of the document

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getKeyValueInstance('s3://....');

$size = 1024;
$offset = 0;
$data = $s3->getChunk("object_name", [], 1024, 0);
while (strlen($data) <= $size) {
    $data .= $s3->getChunk("object_name", [], 1024, 0);
}
```
