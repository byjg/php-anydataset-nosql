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

## Type Definitions

When using the put/get/remove methods, you need to provide type information through the `options` parameter to define the data model.

This library provides a `DynamoDbAttributeType` enum for defining attribute types, making your code more maintainable and less error-prone:

```php
<?php
use ByJG\AnyDataset\NoSql\Enum\DynamoDbAttributeType;

// Example of options using the enum
$options = [
    "KeyName" => "id",
    "Types" => [
        "id" => DynamoDbAttributeType::NUMBER->value,     // 'N'
        "time" => DynamoDbAttributeType::NUMBER->value,   // 'N'
        "error" => DynamoDbAttributeType::STRING->value,  // 'S'
        "message" => DynamoDbAttributeType::STRING->value // 'S'
    ]
];
```

### Available Attribute Types

The `DynamoDbAttributeType` enum provides the following types:

| Enum Case  | Value  | Description                            |
|------------|--------|----------------------------------------|
| NUMBER     | 'N'    | Represents a number                    |
| STRING     | 'S'    | Represents a string                    |
| BINARY     | 'B'    | Represents binary data                 |
| BOOLEAN    | 'BOOL' | Represents a boolean value             |
| NULL       | 'NULL' | Represents a null value                |
| MAP        | 'M'    | Represents a map (nested attributes)   |
| LIST       | 'L'    | Represents a list (ordered collection) |
| STRING_SET | 'SS'   | Represents a set of strings            |
| NUMBER_SET | 'NS'   | Represents a set of numbers            |
| BINARY_SET | 'BS'   | Represents a set of binary values      |

## Basic Operations

### Inserting/Updating data

```php
<?php
use ByJG\AnyDataset\NoSql\Enum\DynamoDbAttributeType;
use ByJG\AnyDataset\NoSql\Factory;

$dynamodb = Factory::getInstance('dynamodb://....');

// Define types using the enum
$options = [
    "KeyName" => "id",
    "Types" => [
        "id" => DynamoDbAttributeType::NUMBER->value,
        "time" => DynamoDbAttributeType::NUMBER->value,
        "error" => DynamoDbAttributeType::STRING->value,
        "message" => DynamoDbAttributeType::STRING->value
    ]
];

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
use ByJG\AnyDataset\NoSql\Enum\DynamoDbAttributeType;
use ByJG\AnyDataset\NoSql\Factory;

$dynamodb = Factory::getInstance('dynamodb://....');

$options = [
    "KeyName" => "id",
    "Types" => [
        "id" => DynamoDbAttributeType::NUMBER->value,
    ]
];

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
use ByJG\AnyDataset\NoSql\Enum\DynamoDbAttributeType;
use ByJG\AnyDataset\NoSql\Factory;

$dynamodb = Factory::getInstance('dynamodb://....');

$options = [
    "KeyName" => "id",
    "Types" => [
        "id" => DynamoDbAttributeType::NUMBER->value,
    ]
];

if ($dynamodb->has(1201, $options)) {
    echo "Key exists!";
}
```

### Removing a value

```php
<?php
use ByJG\AnyDataset\NoSql\Enum\DynamoDbAttributeType;
use ByJG\AnyDataset\NoSql\Factory;

$dynamodb = Factory::getInstance('dynamodb://....');

$options = [
    "KeyName" => "id",
    "Types" => [
        "id" => DynamoDbAttributeType::NUMBER->value,
    ]
];

$dynamodb->remove(1201, $options);
```

## Querying Data

To get a list of objects, you need to use either `KeyConditions` (for queries on the primary key) or `ScanFilter` (for scanning the entire table) in the options array.

### Query using KeyConditions

```php
<?php
use ByJG\AnyDataset\NoSql\Enum\DynamoDbAttributeType;
use ByJG\AnyDataset\NoSql\Factory;

$dynamodb = Factory::getInstance('dynamodb://....');

$options = [
   "KeyConditions" => [
       "id" => [
           "AttributeValueList" => [
               [DynamoDbAttributeType::NUMBER->value => "1201"]
           ],
           "ComparisonOperator" => "EQ"
       ]
   ],
   "Types" => [
       "id" => DynamoDbAttributeType::NUMBER->value,
       "time" => DynamoDbAttributeType::NUMBER->value,
       "error" => DynamoDbAttributeType::STRING->value,
       "message" => DynamoDbAttributeType::STRING->value
   ]
];

$iterator = $dynamodb->getIterator($options);
print_r($iterator->toArray());
```

### Scan using ScanFilter

```php
<?php
use ByJG\AnyDataset\NoSql\Enum\DynamoDbAttributeType;
use ByJG\AnyDataset\NoSql\Factory;

$dynamodb = Factory::getInstance('dynamodb://....');

$options = [
   "ScanFilter" => [
       "error" => [
           "AttributeValueList" => [
               [DynamoDbAttributeType::STRING->value => "Executive overflow"]
           ],
           "ComparisonOperator" => "EQ"
       ]
   ],
   "Types" => [
       "id" => DynamoDbAttributeType::NUMBER->value,
       "time" => DynamoDbAttributeType::NUMBER->value,
       "error" => DynamoDbAttributeType::STRING->value,
       "message" => DynamoDbAttributeType::STRING->value
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
