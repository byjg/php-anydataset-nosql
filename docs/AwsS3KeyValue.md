---
sidebar_position: 3
title: AWS S3
description: AWS S3 and S3-compatible storage as a Key/Value store
---

# AWS S3

Amazon S3 (Simple Storage Service) is an object storage service that offers industry-leading scalability, data
availability, security, and performance. This driver allows you to interact with S3 and S3-compatible storage services (
like MinIO, Ceph) through a KeyValue interface.

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getInstance('s3://access_key:secret_key@region/bucket');
```

The full connection string format:

```text
s3://access_key:secret_key@region/bucket?option1=value1&option2=value2
```

Example:

```text
s3://AKA12345678899:aaaaaaaaaaaaaaaaaaaaaaaaa@us-east-1/mybucket
```

## Connection Options

You can add any additional parameters supported by the S3 API to the query string. For a comprehensive list, refer to:
- [AWS SDK for PHP Client Configuration](https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.AwsClient.html#___construct)
- [S3 Client Configuration](https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.S3.S3Client.html#___construct)

### Custom Endpoint

One common parameter is `endpoint`, which allows you to set a custom endpoint for working with S3-compatible services like MinIO, Ceph, or a local test environment:

```text
s3://access_key:secret_key@us-east-1/mybucket?endpoint=http://localhost:9000
```

### Bucket Creation

:::tip Auto-create Bucket
The library provides a special parameter `create` that will automatically create the bucket if it doesn't exist:

```text
s3://access_key:secret_key@us-east-1/mybucket?create=true
```

:::

You can combine multiple parameters:

```text
s3://access_key:secret_key@us-east-1/mybucket?create=true&endpoint=http://localhost:9000
```

## Basic Operations

### List all objects

List all objects in the bucket:

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getInstance('s3://....');
$iterator = $s3->getIterator();
print_r($iterator->toArray());
```

You can filter objects by prefix and set other options:

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getInstance('s3://....');
$iterator = $s3->getIterator([
    'Prefix' => 'folder/', // List objects with this prefix
    'MaxKeys' => 100       // Maximum number of keys to retrieve
]);
print_r($iterator->toArray());
```

### Inserting/Updating data

Store an object with a specific key:

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getInstance('s3://....');
$s3->put("object_name", "value");
```

You can also specify additional options:

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getInstance('s3://....');
$s3->put(
    "object_name", 
    "value",
    [
        'ContentType' => 'text/plain',
        'ACL' => 'public-read'
    ]
);
```

### Checking if a key exists

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getInstance('s3://....');
if ($s3->has("object_name")) {
    echo "Object exists!";
}
```

### Retrieving a value

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getInstance('s3://....');
$value = $s3->get("object_name");
```

With options:

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getInstance('s3://....');
$value = $s3->get("object_name", [
    'ResponseContentType' => 'application/json'
]);
```

### Retrieving portions of a large object

For large objects, you can retrieve specific chunks to manage memory usage:

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getInstance('s3://....');

// Get 1024 bytes starting from offset 0
$chunk1 = $s3->getChunk("object_name", [], 1024, 0);
// Get the next 1024 bytes (from offset 1024)
$chunk2 = $s3->getChunk("object_name", [], 1024, 1024);

// Example of reading a large file in chunks
$size = 1024;
$offset = 0;
$data = "";
do {
    $chunk = $s3->getChunk("object_name", [], $size, $offset);
    $data .= $chunk;
    $offset += $size;
} while (strlen($chunk) == $size);
```

### Removing a value

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getInstance('s3://....');
$s3->remove("object_name");
```

### Renaming a key

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getInstance('s3://....');
$s3->rename("old_key_name", "new_key_name");
```

## Further Reading

- [AWS SDK for PHP - S3 Documentation](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/s3-examples.html)
- [S3 Client API Reference](https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.S3.S3Client.html)

----
[Open source ByJG](http://opensource.byjg.com)