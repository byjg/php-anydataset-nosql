---
sidebar_key: anydataset-nosql
sidebar_category: data-access
tags: [php, anydataset, databases]
---

# NoSQL Database Abstraction

One interface for document databases and one for key/value stores, whatever the service behind them.
The implementation supports:

[![Sponsor](https://img.shields.io/badge/Sponsor-%23ea4aaa?logo=githubsponsors&logoColor=white&labelColor=0d1117)](https://github.com/sponsors/byjg)
[![Build Status](https://github.com/byjg/php-anydataset-nosql/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/byjg/php-anydataset-nosql/actions/workflows/phpunit.yml)
[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](https://opensource.byjg.com)
[![Install MCP Server](https://img.shields.io/badge/Install-MCP_Server-8A2BE2?logo=modelcontextprotocol&logoColor=white)](https://opensource.byjg.com/docs/ai/mcpserver-byjg-docs/)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/php-anydataset-nosql/)
[![GitHub license](https://img.shields.io/github/license/byjg/php-anydataset-nosql.svg)](https://opensource.byjg.com/license/)
[![GitHub release](https://img.shields.io/github/release/byjg/php-anydataset-nosql.svg)](https://github.com/byjg/php-anydataset-nosql/releases/)

- MongoDB (document-based)
- AWS DynamoDB (key/value)
- S3-Like Storage (key/value)
- Cloudflare KV (key/value)

Anydataset is an agnostic data source abstraction layer in PHP. See more about Anydataset [here](https://opensource.byjg.com/php/anydataset).

## Why

MongoDB, DynamoDB, S3 and Cloudflare KV each come with their own SDK and their own way to
read, write and list. Storing a file in S3 today and in Cloudflare KV tomorrow means
rewriting every call.

Anydataset NoSQL defines two contracts, `NoSqlInterface` for documents and
`KeyValueInterface` for key/value stores, and picks the service from the connection URI:

```php
$store = Factory::getInstance('s3://key:secret@us-east-1/my-bucket');        // AWS S3
$store = Factory::getInstance('kv://user:token@account/namespace');          // Cloudflare KV
$store = Factory::getInstance('dynamodb://key:secret@host/table');           // DynamoDB

$store->put('invoice-42', $pdf);
$pdf = $store->get('invoice-42');
```

- **Change the service with the URI**, not the code that uses it.
- **Same iterator as the rest of AnyDataset.** `getIterator()` returns the same rows as a
  database or a file, and document queries use the same `IteratorFilter`.

## Features

- Access both document-based and key/value repositories with consistent interfaces
- NoSQL document-based databases accessed through `NoSqlInterface`
- Key/Value stores accessed through `KeyValueInterface`
- Unified connection string format based on URIs
- Type-safe DynamoDB attribute definitions using enums

## Connection Based on URI

The connection string for databases is based on URL.

See below the current implemented drivers:

| Datasource                                  | Connection String                                        |
|---------------------------------------------|----------------------------------------------------------|
| [MongoDB](docs/mongodb.md)                  | mongodb://username:password@hostname:port/database       |
| [S3](docs/aws-s3-key-value.md)                 | s3://accesskey:secretkey@region/bucket?params            |
| [Cloudflare KV](docs/cloudflare-kv.md)       | kv://username:password@accountid/namespaceid             |
| [AWS DynamoDB](docs/aws-dynamodb-key-value.md) | dynamodb://accesskey:secretkey@hostname/tablename?params |

## Documentation

### NoSQL Drivers

- [MongoDB](docs/mongodb.md) - Document-based NoSQL database

### Key/Value Drivers

- [AWS DynamoDB](docs/aws-dynamodb-key-value.md) - Key/Value store with type-safe attribute definitions
- [AWS S3](docs/aws-s3-key-value.md) - Object storage as Key/Value store
- [Cloudflare KV](docs/cloudflare-kv.md) - Cloudflare Workers KV storage

### Additional Features

- [Running Tests](docs/tests.md) - How to run the test suite

## Installation

Just type: 

```bash
composer require "byjg/anydataset-nosql"
```

## Running Tests

```bash
docker compose up -d
vendor/bin/phpunit
```

More details are in [Running Tests](docs/tests.md).

----
[Open source ByJG](https://opensource.byjg.com)
