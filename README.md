# AnyDataset-NoSql


[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](http://opensource.byjg.com)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/anydataset-nosql/)
[![GitHub license](https://img.shields.io/github/license/byjg/anydataset-nosql.svg)](https://opensource.byjg.com/opensource/licensing.html)
[![GitHub release](https://img.shields.io/github/release/byjg/anydataset-nosql.svg)](https://github.com/byjg/anydataset-nosql/releases/)
[![Build Status](https://travis-ci.com/byjg/anydataset-nosql.svg?branch=master)](https://travis-ci.com/byjg/anydataset-nosql)

Anydataset NoSQL standardize the access to non-relational databases/repositories and treat them as Key/Value.
The implementation can work with:

- MongoDB
- Cloudflare KV
- S3
- DynamoDB

Anydataset is an agnostic data source abstraction layer in PHP. See more about Anydataset [here](https://opensource.byjg.com/php/anydataset).

## Features

- Access as Key/Value repositories different datasource
- Allow put and get data
- Simplified way to connect to the datasources

## Connection Based on URI

The connection string for databases is based on URL.

See below the current implemented drivers:

| Datasource                                  | Connection String                                        |
|---------------------------------------------|----------------------------------------------------------|
| [MongoDB](docs/MongoDB.md)                  | mongodb://username:password@hostname:port/database       |
| [Cloudflare KV](docs/CloudflareKV.md)       | kv://username:password@accountid/namespaceid             |
| [S3](docs/AwsS3KeyValue.md)                 | s3://accesskey:secretkey@region/bucket?params            |
| [AWS DynamoDB](docs/AwsDynamoDbKeyValue.md) | dynamodb://accesskey:secretkey@hostname/tablename?params |


## Examples

Check implementation examples on [https://opensource.byjg.com/php/anydataset-nosql](https://opensource.byjg.com/php/anydataset-nosql)

## Install

Just type: 

```bash
composer require "byjg/anydataset-nosql=^4.9"
```

## Running Unit tests

```bash
docker-compose up -d
export MONGODB_CONNECTION="mongodb://127.0.0.1/test"
export S3_CONNECTION="s3://aaa:12345678@us-east-1/mybucket?create=true&endpoint=http://127.0.0.1:9000"
export DYNAMODB_CONNECTION="dynamodb://access_key:secret_key@us-east-1/tablename?endpoint=http://127.0.0.1:8000"
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
