# AnyDataset-NoSql

[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](http://opensource.byjg.com)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/anydataset-nosql/)
[![GitHub license](https://img.shields.io/github/license/byjg/anydataset-nosql.svg)](https://opensource.byjg.com/opensource/licensing.html)
[![GitHub release](https://img.shields.io/github/release/byjg/anydataset-nosql.svg)](https://github.com/byjg/anydataset-nosql/releases/)
[![Build Status](https://travis-ci.com/byjg/anydataset-nosql.svg?branch=master)](https://travis-ci.com/byjg/anydataset-nosql)


NoSql abstraction dataset. Anydataset is an agnostic data source abstraction layer in PHP. 

See more about Anydataset [here](https://opensource.byjg.com/php/anydataset).

## Examples

Check implementation examples on [https://opensource.byjg.com/php/anydataset-nosql]()

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
