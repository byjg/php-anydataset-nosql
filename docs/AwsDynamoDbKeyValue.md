---
sidebar_position: 2
---

# AWS DynamoDB

AWS DynamoDB is a managed NoSQL database service that provides fast and predictable performance with seamless scalability.

```php
<?php
$dynamodb = \ByJG\AnyDataset\NoSql\Factory::getInstance('dynamodb://access_key:secret_key@region/tablename');
```

The full connection string format:

```
dynamodb://access_key:secret_key@region/tablename?option1=value1&option2=value2
```

Example:

```
dynamodb://AKA12345678899:aaaaaaaaaaaaaaaaaaaaaaaaa@us-east-1/mytable
```

You can add any extra arguments supported by the DynamoDB API. You can get a full list here:
- [AWS SDK for PHP Client Configuration](https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.AwsClient.html#___construct)

One of the most common parameters is `endpoint`, which allows you to set a custom endpoint to access a DynamoDB compatible interface, such as DynamoDB Local for development or testing.

Example with a custom endpoint: 

```
dynamodb://access_key:secret_key@us-east-1/tablename?endpoint=http://localhost:8000
```


## DynamoDB Data Structure

DynamoDB stores information using a specific attribute format that differs from typical object structures.

For example, a DynamoDB native representation looks like this:

```
[
    'id'      => ['N' => '1201'],
    'time'    => ['N' => $time],
    'error'   => ['S' => 'Executive overflow'],
    'message' => ['S' => 'no vacant areas']
]
```

This library abstracts this format to let you use a more familiar representation:

```
[
    'id'      => 1201,
    'time'    => $time,
    'error'   => 'Executive overflow',
    'message' => 'no vacant areas'
]
```

When using the put/get/remove methods, you need to provide type information through the `options` parameter to define the data model.

The options array requires two keys:
- `KeyName`: The primary key name (currently only supports tables with a single key)
- `Types`: Defines field names and their DynamoDB types (N = number, S = string, etc.)

Example options:

```php
<?php
$options = [
    "KeyName" => "id",
    "Types" => [
        "id" => "N",     // Number
        "time" => "N",   // Number
        "error" => "S",  // String
        "message" => "S" // String
    ]
];
```

## Basic Operations

### Inserting/Updating data

```php
<?php
$dynamodb = \ByJG\AnyDataset\NoSql\Factory::getInstance('dynamodb://....');
$dynamodb->put(
    1201,  // Primary key value
    [
        "time" => 1234567899,
        "error" => 'Executive overflow',
        "message" => "No Vacant Areas"
    ],
    $options  // Type definitions
);
```

### Retrieving a value

```php
<?php
$dynamodb = \ByJG\AnyDataset\NoSql\Factory::getInstance('dynamodb://....');
$value = $dynamodb->get(1201, $options);

/* Returns:
[
    'id'      => 1201,
    'time'    => 1234567899,
    'error'   => 'Executive overflow',
    'message' => 'No Vacant Areas'
]
*/
```

### Checking if a key exists

```php
<?php
$dynamodb = \ByJG\AnyDataset\NoSql\Factory::getInstance('dynamodb://....');
if ($dynamodb->has(1201, $options)) {
    echo "Key exists!";
}
```

### Removing a value

```php
<?php
$dynamodb = \ByJG\AnyDataset\NoSql\Factory::getInstance('dynamodb://....');
$dynamodb->remove(1201, $options);
```

## Querying Data

To get a list of objects, you need to use either `KeyConditions` (for queries on the primary key) or `ScanFilter` (for scanning the entire table) in the options array.

### Query using KeyConditions

```php
<?php
$dynamodb = \ByJG\AnyDataset\NoSql\Factory::getInstance('dynamodb://....');

$options = [
   "KeyConditions" => [
       "id" => [
           "AttributeValueList" => [
               ["N" => "1201"]
           ],
           "ComparisonOperator" => "EQ"
       ]
   ],
   "Types" => [
       "id" => "N",
       "time" => "N",
       "error" => "S",
       "message" => "S"
   ]
];

$iterator = $dynamodb->getIterator($options);
print_r($iterator->toArray());
```

### Scan using ScanFilter

```php
<?php
$dynamodb = \ByJG\AnyDataset\NoSql\Factory::getInstance('dynamodb://....');

$options = [
   "ScanFilter" => [
       "error" => [
           "AttributeValueList" => [
               ["S" => "Executive overflow"]
           ],
           "ComparisonOperator" => "EQ"
       ]
   ],
   "Types" => [
       "id" => "N",
       "time" => "N",
       "error" => "S",
       "message" => "S"
   ]
];

$iterator = $dynamodb->getIterator($options);
print_r($iterator->toArray());
```

## Further Reading

- [AWS SDK for PHP - DynamoDB Documentation](https://docs.aws.amazon.com/aws-sdk-php/v2/guide/service-dynamodb.html)
- [DynamoDB Client API Reference](https://docs.aws.amazon.com/aws-sdk-php/v2/api/class-Aws.DynamoDb.DynamoDbClient.html)

----
[Open source ByJG](http://opensource.byjg.com)
