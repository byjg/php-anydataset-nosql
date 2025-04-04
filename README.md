# AnyDataset-NoSql

[![Build Status](https://github.com/byjg/php-anydataset-nosql/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/byjg/php-anydataset-nosql/actions/workflows/phpunit.yml)
[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](http://opensource.byjg.com)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/php-anydataset-nosql/)
[![GitHub license](https://img.shields.io/github/license/byjg/php-anydataset-nosql.svg)](https://opensource.byjg.com/opensource/licensing.html)
[![GitHub release](https://img.shields.io/github/release/byjg/php-anydataset-nosql.svg)](https://github.com/byjg/php-anydataset-nosql/releases/)

Anydataset NoSQL standardizes the access to non-relational databases/repositories and provides a consistent interface for both NoSQL document databases and Key/Value stores.
The implementation supports:

- MongoDB (document-based)
- AWS DynamoDB (key/value)
- S3-Like Storage (key/value)
- Cloudflare KV (key/value)

Anydataset is an agnostic data source abstraction layer in PHP. See more about Anydataset [here](https://opensource.byjg.com/php/anydataset).

## Features

- Access both document-based and key/value repositories with consistent interfaces
- NoSQL document-based databases accessed through `NoSqlInterface`
- Key/Value stores accessed through `KeyValueInterface`
- Unified connection string format based on URIs
- Built-in caching capabilities with PSR-16 compatibility

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

- [MongoDB](docs/MongoDB.md)
- [AWS DynamoDB](docs/AwsDynamoDbKeyValue.md)
- [S3-Like Storage](docs/AwsS3KeyValue.md)
- [Cloudflare KV](docs/CloudFlareKV.md)
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
