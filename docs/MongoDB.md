---
sidebar_position: 1
---

# MongoDB

```php
<?php
$mongo = \ByJG\AnyDataset\NoSql\Factory::getInstance('mongodb://server');
```

The full connection string can be:

```
mongodb://username:password@server1,server2,server3/dbname?param1=value1&param2=value2
```

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

## Updating a document

Automatically is created the field 'created' and 'update' with the MongoDate() of the current insert.
Because there is no ID (first parameter) is an INSERT; 

```php
<?php
$mongo = \ByJG\AnyDataset\NoSql\Factory::getInstance('mongodb://server');
$document = new \ByJG\AnyDataset\NoSql\NoSqlDocument(
    'someid',
    'mycollection',
    [
        'field1' => 'value1',
        'field2' => 'value2',
        'field3' => 'value3',
    ]
);
$mongo->save($document);
```

Automatically the field 'updated' is updated with the MongoDate() of the current update.
Because there is an ID (first parameter) is an UPDATE; 


## Querying the collection

Querying the database will result a GenericIterator. It will be compatible with all objects.

### Retrieve a document by Id

```php
<?php
$mongo = \ByJG\AnyDataset\NoSql\Factory::getInstance('mongodb://server');
$document = $mongo->getDocumentById($id);
if (!empty($document)) {
    print_r($document->getIdDocument());
    print_r($document->getDocument());
}
```


### Retrieve all data

```php
<?php
$mongo = \ByJG\AnyDataset\NoSql\Factory::getInstance('mongodb://server');
$result = $mongo->getDocuments(null, 'mycollection');
foreach ($result as $document)
{
    print_r($document->getIdDocument());
    print_r($document->getDocument());
}
```

### Filtering the data

```php
<?php

$filter = new \ByJG\AnyDataset\Core\IteratorFilter();
$filter->addRelation('field', \ByJG\AnyDataset\Core\Enum\Relation::EQUAL, 'value');

$result = $mongo->getDocuments($filter, 'mycollection');
foreach ($result as $document)
{
    // Do something
}
```


### Full Connection String

```text
mongodb://username:password@server:27017/dbname?uri.option1=value1&driver.option2=value2
```

The list of parameters can be found in the [PHP MongoDB Driver documentation](https://www.php.net/manual/en/mongodb-driver-manager.construct.php).

Parameters started with `uri.` are passed to the MongoDB URI connection string.
Parameters started with `driver.` are passed to the MongoDB driver connection string.
Any other parameters will throw an exception.


----
[Open source ByJG](http://opensource.byjg.com)
