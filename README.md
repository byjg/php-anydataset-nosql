# AnyDataset-NoSql

[![Build Status](https://github.com/byjg/php-anydataset-nosql/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/byjg/php-anydataset-nosql/actions/workflows/phpunit.yml)
[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](http://opensource.byjg.com)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/php-anydataset-nosql/)
[![GitHub license](https://img.shields.io/github/license/byjg/php-anydataset-nosql.svg)](https://opensource.byjg.com/opensource/licensing.html)
[![GitHub release](https://img.shields.io/github/release/byjg/php-anydataset-nosql.svg)](https://github.com/byjg/php-anydataset-nosql/releases/)

Anydataset NoSQL standardize the access to non-relational databases/repositories and treat them as Key/Value.
The implementation can work with:

- S3-Like Storage
- MongoDB
- Cloudflare KV
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
| [S3](docs/AwsS3KeyValue.md)                 | s3://accesskey:secretkey@region/bucket?params            |
| [Cloudflare KV](docs/CloudFlareKV.md)       | kv://username:password@accountid/namespaceid             |
| [AWS DynamoDB](docs/AwsDynamoDbKeyValue.md) | dynamodb://accesskey:secretkey@hostname/tablename?params |


## Topics

- [S3-Like Storage](docs/AwsS3KeyValue.md)
- [MongoDB](docs/MongoDB.md)
- [Cloudflare KV](docs/CloudFlareKV.md)
- [AWS DynamoDB](docs/AwsDynamoDbKeyValue.md)
- [Cache Store](docs/cache.md)
- [Running Tests](docs/tests.md)

## Install

Just type: 

```bash
composer require "byjg/anydataset-nosql"
```

## Dependencies

```mermaid
flowchart TD
   byjg/anydataset-nosql --> ext-curl
   byjg/anydataset-nosql --> aws/aws-sdk-php
   byjg/anydataset-nosql --> byjg/anydataset
   byjg/anydataset-nosql --> byjg/anydataset-array
   byjg/anydataset-nosql --> byjg/serializer
   byjg/anydataset-nosql --> byjg/webrequest
   byjg/anydataset-nosql --> byjg/cache-engine
   byjg/anydataset-nosql --> ext-json
```

----
[Open source ByJG](http://opensource.byjg.com)
