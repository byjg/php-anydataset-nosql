# AnyDataset-NoSql

[![Build Status](https://github.com/byjg/php-anydataset-nosql/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/byjg/php-anydataset-nosql/actions/workflows/phpunit.yml)
[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](http://opensource.byjg.com)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/php-anydataset-nosql/)
[![GitHub license](https://img.shields.io/github/license/byjg/php-anydataset-nosql.svg)](https://opensource.byjg.com/opensource/licensing.html)
[![GitHub release](https://img.shields.io/github/release/byjg/php-anydataset-nosql.svg)](https://github.com/byjg/php-anydataset-nosql/releases/)

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
| [MongoDB](MongoDB.md)                  | mongodb://username:password@hostname:port/database       |
| [Cloudflare KV](CloudFlareKV.md)       | kv://username:password@accountid/namespaceid             |
| [S3](AwsS3KeyValue.md)                 | s3://accesskey:secretkey@region/bucket?params            |
| [AWS DynamoDB](AwsDynamoDbKeyValue.md) | dynamodb://accesskey:secretkey@hostname/tablename?params |


## Examples

Check implementation examples on [https://opensource.byjg.com/php/anydataset-nosql](https://opensource.byjg.com/php/anydataset-nosql)

## Install

Just type: 

```bash
composer require "byjg/anydataset-nosql"
```

## Running Unit tests

```bash
docker-compose up -d
export MONGODB_CONNECTION="mongodb://127.0.0.1/test"
export S3_CONNECTION="s3://aaa:12345678@us-east-1/mybucket?create=true&endpoint=http://127.0.0.1:4566"
export DYNAMODB_CONNECTION="dynamodb://accesskey:secretkey@us-east-1/tablename?endpoint=http://127.0.0.1:8000"
vendor/bin/phpunit
```


### Setup MongoDB for the unit test

Set the environment variable:

- MONGODB_CONNECTION = "mongodb://127.0.0.1/test"

### Setup AWS DynamoDb for the unit test

Set the environment variable:
 
- DYNAMODB_CONNECTION = "dynamodb://accesskey:secretkey@region/tablename"

### Setup AWS S3 for the unit test

Set the environment variable:
 
- S3_CONNECTION = "s3://accesskey:secretkey@region/bucketname"


### Cloudflare KV

Set the environment variable:
 
- CLOUDFLAREKV_CONNECTION = "kv://email:authkey@accountid/namespaceid"

## Dependencies

```mermaid
flowchart TD
   byjg/anydataset-nosql --> ext-curl
   byjg/anydataset-nosql --> aws/aws-sdk-php
   byjg/anydataset-nosql --> byjg/anydataset
   byjg/anydataset-nosql --> byjg/anydataset-array
   byjg/anydataset-nosql --> byjg/serializer
   byjg/anydataset-nosql --> byjg/webrequest
   byjg/anydataset-nosql --> ext-json
```

----
[Open source ByJG](http://opensource.byjg.com)
