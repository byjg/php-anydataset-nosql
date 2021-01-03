# AnyDataset-NoSql

[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg.com-brightgreen.svg)](http://opensource.byjg.com)
[![Build Status](https://travis-ci.org/byjg/anydataset-nosql.svg?branch=master)](https://travis-ci.org/byjg/anydataset-nosql)


NoSql abstraction dataset. Anydataset is an agnostic data source abstraction layer in PHP. 

See more about Anydataset [here](https://opensource.byjg.com/anydataset).

## Examples

- [Read More about using MongoDb](MongoDB.md)
- [Read More about using Aws DynamoDb Key Value](AwsDynamoDbKeyValue.md)
- [Read More about using Aws S3 Key Value](AwsS3KeyValue.md)
- [Read More about using CloudFlare KV](CloudFlareKV.md)

## Install

Just type: 

```bash
composer require "byjg/anydataset-nosql=4.1.*"
```

## Running Unit tests

```bash
vendor/bin/phpunit
```


### Setup MongoDB for the unit test

Set the environment variable:

- MONGODB_CONNECTION = "mongodb://127.0.0.1/test"

### Setup AWS DynamoDb for the unit test

Set the environment variable:
 
- DYNAMODB_CONNECTION = "dynamodb://access_key:secret_key@region/tablename"

### Setup AWS S3 for the unit test

Set the environment variable:
 
- S3_CONNECTION = "s3://access_key:secret_key@region/bucketname"


### Cloudflare KV

Set the environment variable:
 
- CLOUDFLAREKV_CONNECTION = "kv://email:authkey@accountid/namespaceid"



----
[Open source ByJG](http://opensource.byjg.com)
