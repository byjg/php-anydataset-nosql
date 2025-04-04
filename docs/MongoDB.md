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
mongodb://username:password@server1,server2,server3/dbname?uri.param1=value1&driver.param2=value2
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

When a document is inserted, the fields 'created' and 'updated' are automatically added with the current date.
Because there is no ID (first parameter) provided, this is treated as an INSERT operation.

## Updating a document

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

When updating a document, the field 'updated' is automatically updated with the current date.
Because an ID (first parameter) is provided, this is treated as an UPDATE operation.


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

Note that the collection name is required as the second parameter.

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

You can use the IteratorFilter to filter documents:

```php
<?php
$filter = new \ByJG\AnyDataset\Core\IteratorFilter();
$filter->addRelation('field', \ByJG\AnyDataset\Core\Enum\Relation::EQUAL, 'value');

$mongo = \ByJG\AnyDataset\NoSql\Factory::getInstance('mongodb://server');
$result = $mongo->getDocuments($filter, 'mycollection');
foreach ($result as $document)
{
    // Do something
}
```

The MongoDbDriver supports the following relations:
- EQUAL
- GREATER_THAN
- LESS_THAN
- GREATER_OR_EQUAL_THAN
- LESS_OR_EQUAL_THAN
- NOT_EQUAL
- STARTS_WITH
- CONTAINS
- IN
- NOT_IN

### Deleting Documents

```php
<?php
// Delete by ID
$mongo->deleteDocumentById($id, 'mycollection');

// Delete multiple documents by filter
$filter = new \ByJG\AnyDataset\Core\IteratorFilter();
$filter->addRelation('field', \ByJG\AnyDataset\Core\Enum\Relation::EQUAL, 'value');
$mongo->deleteDocuments($filter, 'mycollection');
```

### Updating Multiple Documents

```php
<?php
$filter = new \ByJG\AnyDataset\Core\IteratorFilter();
$filter->addRelation('field', \ByJG\AnyDataset\Core\Enum\Relation::EQUAL, 'value');

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

The list of parameters can be found in the [PHP MongoDB Driver documentation](https://www.php.net/manual/en/mongodb-driver-manager.construct.php).

Parameters must be prefixed with:
- `uri.` - passed to the MongoDB URI connection string.
- `driver.` - passed to the MongoDB driver connection string.

Any other parameters will throw an exception.


----
[Open source ByJG](http://opensource.byjg.com)
