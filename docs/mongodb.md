---
sidebar_position: 1
title: MongoDB
description: Document-based NoSQL database driver for AnyDataset
---

# MongoDB

```php
<?php
$mongo = \ByJG\AnyDataset\NoSql\Factory::getInstance('mongodb://server');
```

The full connection string format:

```text
mongodb://username:password@server/dbname?uri.param1=value1&driver.param2=value2
```

Percent-encode special characters (such as `@`, `:`, `/`, `%`, `?` and `#`) in the username and
password, e.g. with `rawurlencode()`. The password `p@ss:word` becomes `p%40ss%3Aword`:

```text
mongodb://username:p%40ss%3Aword@server/dbname
```

The credentials are sent only when both the username and the password are present and not empty
(`"0"` is a valid value); otherwise the connection is opened without authentication.

:::note Replica sets
Only one host is supported in the connection string; a host list such as `server1,server2,server3`
throws an `InvalidArgumentException`. To connect to a replica set, pass one member as the seed host
and the replica set name; the driver discovers the other members from it:

```text
mongodb://username:password@server1/dbname?uri.replicaSet=rs0
```
:::

## Inserting data to a Collection

To insert data:

```php
<?php
$mongo = \ByJG\AnyDataset\NoSql\Factory::getInstance('mongodb://server');
$document = new \ByJG\AnyDataset\NoSql\NoSqlDocument(
    null,
    'mycollection',
    [
        'field1' => 'value1',
        'field2' => 'value2',
        'field3' => 'value3',
    ]
);
$mongo->save($document);
```

:::info
When a document is inserted, the fields `createdAt` and `updatedAt` are automatically added with the current date.
Because there is no ID (first parameter) provided, this is treated as an INSERT operation.
:::

## Updating a document

```php
<?php
$mongo = \ByJG\AnyDataset\NoSql\Factory::getInstance('mongodb://server');
$document = new \ByJG\AnyDataset\NoSql\NoSqlDocument(
    '5f1d7f1b2c3a4b5c6d7e8f90',
    'mycollection',
    [
        'field1' => 'value1',
        'field2' => 'value2',
        'field3' => 'value3',
    ]
);
$mongo->save($document);
```

:::info
When updating a document, the field `updatedAt` is automatically updated with the current date.
Because an ID (first parameter) is provided, this is treated as an UPDATE operation (an upsert: the document is
created if the ID does not exist). The ID must be a valid MongoDB ObjectId string.
:::


## Querying the collection

Querying the database will return documents as NoSqlDocument objects.

### Retrieve a document by Id

```php
<?php
$mongo = \ByJG\AnyDataset\NoSql\Factory::getInstance('mongodb://server');
$document = $mongo->getDocumentById($id, 'mycollection');
if (!empty($document)) {
    print_r($document->getIdDocument());
    print_r($document->getDocument());
}
```

:::note
The collection name is required as the second parameter.
:::

### Retrieve all data

```php
<?php
$mongo = \ByJG\AnyDataset\NoSql\Factory::getInstance('mongodb://server');
$result = $mongo->getDocuments(new \ByJG\AnyDataset\Core\IteratorFilter(), 'mycollection');
foreach ($result ?? [] as $document)
{
    print_r($document->getIdDocument());
    print_r($document->getDocument());
}
```

### Filtering the data

You can use the IteratorFilter to filter documents:

```php
<?php
$filter = new \ByJG\AnyDataset\Core\IteratorFilter();
$filter->and('field', \ByJG\AnyDataset\Core\Enum\Relation::Equal, 'value');

$mongo = \ByJG\AnyDataset\NoSql\Factory::getInstance('mongodb://server');
$result = $mongo->getDocuments($filter, 'mycollection'); // null when nothing matches
foreach ($result ?? [] as $document)
{
    // Do something
}
```

:::tip Supported Relations
The MongoDbDriver supports the following filter relations:

- `Equal`
- `GreaterThan`
- `LessThan`
- `GreaterThanOrEqualTo`
- `LessThanOrEqualTo`
- `NotEqual`
- `StartsWith`
- `Contains`
- `In`
- `NotIn`
  :::

### Deleting Documents

```php
<?php
// Delete by ID
$mongo->deleteDocumentById($id, 'mycollection');

// Delete multiple documents by filter
$filter = new \ByJG\AnyDataset\Core\IteratorFilter();
$filter->and('field', \ByJG\AnyDataset\Core\Enum\Relation::Equal, 'value');
$mongo->deleteDocuments($filter, 'mycollection');
```

### Updating Multiple Documents

```php
<?php
$filter = new \ByJG\AnyDataset\Core\IteratorFilter();
$filter->and('field', \ByJG\AnyDataset\Core\Enum\Relation::Equal, 'value');

$data = [
    'field1' => 'new_value1',
    'field2' => 'new_value2'
];

$mongo->updateDocuments($filter, $data, 'mycollection');
```

### Full Connection String

```text
mongodb://username:password@server:27017/dbname?uri.option1=value1&driver.option2=value2
```

:::info Connection Parameters
Parameters must be prefixed with:

- `uri.` - Passed to the MongoDB URI connection string, without the prefix
  (e.g. `uri.replicaSet=rs0&uri.w=majority`)
- `driver.` - Passed to the `$driverOptions` of `MongoDB\Driver\Manager`, without the prefix.
  The values arrive as strings, so only driver options that accept a string can be set this way

Any other parameters will throw an exception.

For the complete list of available parameters, see
the [PHP MongoDB Driver documentation](https://www.php.net/manual/en/mongodb-driver-manager.construct.php).
:::


----
[Open source ByJG](http://opensource.byjg.com)
