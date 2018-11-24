# NoSql AnyDataset

[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg.com-brightgreen.svg)](http://opensource.byjg.com)
[![Build Status](https://travis-ci.org/byjg/anydataset-nosql.svg?branch=master)](https://travis-ci.org/byjg/anydataset-nosql)

## Description

NoSql abstraction dataset. Anydataset is an agnostic data source abstraction layer in PHP. 

See more about Anydataset [here](https://github.com/byjg/anydataset).

## Examples

- [Read More about using MongoDb](MongoDB.md)
- [Read More about using Aws DynamoDb Key Value](AwsDynamoDbKeyValue.md)
- [Read More about using Aws S3 Key Value](AwsS3KeyValue.md)

## Install

Just type: 

```bash
composer require "byjg/anydataset-nosql=4.0.*"
```

## Running Unit tests

### MongoDB

The easiest way to run the tests is:

**Prepare the environment**

```php
npm i
node_modules/.bin/usdocker --refresh
node_modules/.bin/usdocker -v --no-link mongodb up
```

**Run the tests**

```php
vendor/bin/phpunit testsdb/MongoDbDriverTest.php
```
### AWS DynamoDb

You need setup your environment with:
 
- DYNAMODB_CONNECTION = "dynamodb://access_key:secret_key@region/bucketname"

Once defined:

```php
vendor/bin/phpunit testsdb/AwsDynamoDbDriverTest.php
```


### AWS S3

You need setup your environment with:
 
- S3_CONNECTION = "s3://access_key:secret_key@region/bucketname"

Once defined:

```php
vendor/bin/phpunit testsdb/AwsS3DriverTest.php
```


----
[Open source ByJG](http://opensource.byjg.com)
