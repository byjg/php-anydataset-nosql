---
sidebar_key: anydataset-nosql
tags: [php, anydataset, databases]
---

# NoSQL Database Abstraction

Anydataset NoSQL standardizes the access to non-relational databases/repositories and provides a consistent interface for both NoSQL document databases and Key/Value stores.
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

## Features

- Access both document-based and key/value repositories with consistent interfaces
- NoSQL document-based databases accessed through `NoSqlInterface`
- Key/Value stores accessed through `KeyValueInterface`
- Unified connection string format based on URIs
- Built-in caching capabilities with PSR-16 compatibility
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

- [Cache Store](docs/cache.md) - PSR-16 compatible cache layer for Key/Value stores
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
