# AnyDataset-DB

[![Build Status](https://github.com/byjg/php-anydataset-db/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/byjg/php-anydataset-db/actions/workflows/phpunit.yml)
[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](http://opensource.byjg.com)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/php-anydataset-db/)
[![GitHub license](https://img.shields.io/github/license/byjg/php-anydataset-db.svg)](https://opensource.byjg.com/opensource/licensing.html)
[![GitHub release](https://img.shields.io/github/release/byjg/php-anydataset-db.svg)](https://github.com/byjg/php-anydataset-db/releases/)

Anydataset Database Relational abstraction. Anydataset is an agnostic data source abstraction layer in PHP.

See more about Anydataset [here](https://opensource.byjg.com/anydataset).

## Features

- Connection based on URI
- Support and fix code tricks with several databases (MySQL, PostgresSql, MS SQL Server, etc)
- Natively supports Query Cache by implementing a PSR-6 interface
- Supports Connection Routes based on regular expression against the queries, that's mean a select in a table should be
executed in a database and in another table should be executed in another (even if in different DB)

## Connection Based on URI

The connection string for databases is based on URL.

See below the current implemented drivers:

| Database            | Connection String                                 | Factory                   |
|---------------------|---------------------------------------------------|---------------------------|
| Sqlite              | sqlite:///path/to/file                            | getDbRelationalInstance() |
| MySql/MariaDb       | mysql://username:password@hostname:port/database  | getDbRelationalInstance() |
| Postgres            | psql://username:password@hostname:port/database   | getDbRelationalInstance() |
| Sql Server (DbLib)  | dblib://username:password@hostname:port/database  | getDbRelationalInstance() |
| Sql Server (Sqlsrv) | sqlsrv://username:password@hostname:port/database | getDbRelationalInstance() |
| Oracle (OCI)        | oci://username:password@hostname:port/database    | getDbRelationalInstance() |
| Oracle (OCI8)       | oci8://username:password@hostname:port/database   | getDbRelationalInstance() |
| Generic PDO         | pdo://username:password@pdo_driver?PDO_PARAMETERS | getDbRelationalInstance() |

```php
<?php
$conn = \ByJG\AnyDataset\Db\Factory::getDbRelationalInstance("mysql://root:password@10.0.1.10/myschema");
```

## Examples

- [Basic Query and Update](basic-query.md)
- [Cache results](cache.md)
- [Database Transaction](transaction.md)
- [Load Balance and Connection Pooling](load-balance.md)
- [Database Helper](helper.md)

## Advanced Topics

- [Passing Parameters to PDODriver](parameters.md)
- [MySQL SSL Connection](mysql-ssl.md)
- [FreeTDS/Dblib Date Issue](freetds.md)
- [Generic PDO Driver](generic-pdo-driver.md)
- [Running Tests](tests.md)


## Install

Just type:

```bash
composer require "byjg/anydataset"
```

## Dependencies

```mermaid
flowchart TD
    byjg/anydataset-db --> byjg/anydataset-array
    byjg/anydataset-db --> ext-pdo
    byjg/anydataset-db --> byjg/uri
    byjg/anydataset-db --> psr/cache
    byjg/anydataset-db --> psr/log
```

----
[Open source ByJG](http://opensource.byjg.com)
