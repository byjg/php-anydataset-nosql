# AWS S3

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getInstance('s3://access_key:secret_key@region/bucket');
```

The full connection string can be:

```
s3://AKA12345678899:aaaaaaaaaaaaaaaaaaaaaaaaa@us-east-1/mybucket
```

You can add any extra arguments supported by the S3 api. You can get a full list here:
 - https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.AwsClient.html#___construct
 - https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.S3.S3Client.html#___construct

One of the most populars is the parameter `endpoint` where we can set a custom endpoint to access 
an S3 compatible interface. 

An example can be: 

```
s3://AKA12345678899:aaaaaaaaaaaaaaaaaaaaaaaaa@us-east-1/mybucket?endpoint=http://localhost:9000
```

There is a specific parameter called `create` from `anydataset/nosql` that permit create a bucket if 
it doesn't exist.

Example:

```
s3://AKA12345678899:aaaaaaaaaaaaaaaaaaaaaaaaa@us-east-1/mybucket?create=true
```
 

## List all objects

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getInstance('s3://....');
$iterator = $s3->getIterator();
print_r($iterator->toArray());
```

## Inserting/Updating data

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getInstance('s3://....');
$s3->put("object_name", "value");
```

## Retrieve a value

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getInstance('s3://....');
$value = $s3->get("object_name");
```

## Remove a value

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getInstance('s3://....');
$s3->remove("object_name");
```

## Get parts of the document

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getInstance('s3://....');

$size = 1024;
$offset = 0;
$data = $s3->getChunk("object_name", [], 1024, 0);
while (strlen($data) <= $size) {
    $data .= $s3->getChunk("object_name", [], 1024, 0);
}
```

## Rename a key

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getInstance('s3://....');
$s3->rename("object_name", "new_object_name");
```

## Check if a key exists

```php
<?php
$s3 = \ByJG\AnyDataset\NoSql\Factory::getInstance('s3://....');
if ($s3->has("object_name")) {
    echo "exist!";
}
```
----
[Open source ByJG](http://opensource.byjg.com)