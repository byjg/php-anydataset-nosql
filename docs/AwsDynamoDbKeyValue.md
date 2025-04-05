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
        "id" => DynamoDbAttributeType::NUMBER,
        "time" => DynamoDbAttributeType::NUMBER,
        "error" => DynamoDbAttributeType::STRING,
        "message" => DynamoDbAttributeType::STRING
    ]
];
```

### Key Attribute Type Matching

⚠️ **IMPORTANT**: The attribute type you define for your primary key in the `options` array MUST match the attribute type defined in your DynamoDB table schema. Mismatching these types will result in a `ValidationException: Type mismatch for key` error.

For example, if your DynamoDB table defines the `id` attribute as type `NUMBER` (`N`), you must use:

```php
$options = [
    "KeyName" => "id",
    "Types" => [
        "id" => DynamoDbAttributeType::NUMBER, // This must match the table schema
        // other attributes...
    ]
];
```

Similarly, if your table defines the key as `STRING` (`S`), you must use `DynamoDbAttributeType::STRING`.

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
        "id" => DynamoDbAttributeType::NUMBER,
        "time" => DynamoDbAttributeType::NUMBER,
        "error" => DynamoDbAttributeType::STRING,
        "message" => DynamoDbAttributeType::STRING
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

Note that the key value (1201) is passed as the first parameter to the `put` method. You don't need to include this value in the data array as the library will automatically add it for you.

### Retrieving a value

```php
<?php
use ByJG\AnyDataset\NoSql\Enum\DynamoDbAttributeType;
use ByJG\AnyDataset\NoSql\Factory;

$dynamodb = Factory::getInstance('dynamodb://....');

$options = [
    "KeyName" => "id",
    "Types" => [
        "id" => DynamoDbAttributeType::NUMBER,
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
        "id" => DynamoDbAttributeType::NUMBER,
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
        "id" => DynamoDbAttributeType::NUMBER,
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
   "TableName" => "mytable", // Table name is required
   "KeyConditions" => [
       "id" => [
           "AttributeValueList" => [
               [DynamoDbAttributeType::NUMBER->value => "1201"]
           ],
           "ComparisonOperator" => "EQ"
       ]
   ],
   "Types" => [
       "id" => DynamoDbAttributeType::NUMBER,
       "time" => DynamoDbAttributeType::NUMBER,
       "error" => DynamoDbAttributeType::STRING,
       "message" => DynamoDbAttributeType::STRING
   ]
];

$iterator = $dynamodb->getIterator($options);
print_r($iterator->toArray());
```

Note that when using the query operation, the key must match the key used in the table schema, and the type must also match.

### Scan using ScanFilter

```php
<?php
use ByJG\AnyDataset\NoSql\Enum\DynamoDbAttributeType;
use ByJG\AnyDataset\NoSql\Factory;

$dynamodb = Factory::getInstance('dynamodb://....');

$options = [
   "TableName" => "mytable", // Table name is required 
   "ScanFilter" => [
       "error" => [
           "AttributeValueList" => [
               [DynamoDbAttributeType::STRING->value => "Executive overflow"]
           ],
           "ComparisonOperator" => "EQ"
       ]
   ],
   "Types" => [
       "id" => DynamoDbAttributeType::NUMBER,
       "time" => DynamoDbAttributeType::NUMBER,
       "error" => DynamoDbAttributeType::STRING,
       "message" => DynamoDbAttributeType::STRING
   ]
];

$iterator = $dynamodb->getIterator($options);
print_r($iterator->toArray());
```

The Scan operation will search through the entire table, which can be slower but allows you to filter on non-key attributes.

## Working with Complex Data Types

The AWS DynamoDB driver in this library can handle complex data types automatically. You just need to specify the correct type in the options and pass the data in a normal PHP format.

### Boolean Values

Boolean values should be actual PHP booleans:

```php
<?php
use ByJG\AnyDataset\NoSql\Enum\DynamoDbAttributeType;
use ByJG\AnyDataset\NoSql\Factory;

$options = [
    "KeyName" => "id",
    "Types" => [
        "id" => DynamoDbAttributeType::STRING,
        "isActive" => DynamoDbAttributeType::BOOLEAN
    ]
];

$dynamodb = Factory::getInstance('dynamodb://....');
$dynamodb->put(
    'user123',
    [
        "isActive" => true  // Use actual boolean, not string
    ],
    $options
);
```

### Lists

For LIST types, you can use a regular PHP array:

```php
<?php
use ByJG\AnyDataset\NoSql\Enum\DynamoDbAttributeType;
use ByJG\AnyDataset\NoSql\Factory;

$options = [
    "KeyName" => "id",
    "Types" => [
        "id" => DynamoDbAttributeType::STRING,
        "items" => DynamoDbAttributeType::LIST
    ]
];

$dynamodb = Factory::getInstance('dynamodb://....');
$dynamodb->put(
    'order123',
    [
        "items" => ["item1", "item2", "item3"]  // Regular PHP array
    ],
    $options
);
```

### Maps

MAP types can also use a regular PHP associative array:

```php
<?php
use ByJG\AnyDataset\NoSql\Enum\DynamoDbAttributeType;
use ByJG\AnyDataset\NoSql\Factory;

$options = [
    "KeyName" => "id",
    "Types" => [
        "id" => DynamoDbAttributeType::STRING,
        "details" => DynamoDbAttributeType::MAP
    ]
];

$dynamodb = Factory::getInstance('dynamodb://....');
$dynamodb->put(
    'product123',
    [
        "details" => [
            "name" => "Product Name",
            "price" => 99.99,
            "inStock" => true
        ]
    ],
    $options
);
```

### NULL Values

NULL types in DynamoDB can be represented by PHP null:

```php
<?php
use ByJG\AnyDataset\NoSql\Enum\DynamoDbAttributeType;
use ByJG\AnyDataset\NoSql\Factory;

$options = [
    "KeyName" => "id",
    "Types" => [
        "id" => DynamoDbAttributeType::STRING,
        "optional" => DynamoDbAttributeType::NULL
    ]
];

$dynamodb = Factory::getInstance('dynamodb://....');
$dynamodb->put(
    'record123',
    [
        "optional" => null  // Use PHP null
    ],
    $options
);
```

### Set Types

For SET types (STRING_SET, NUMBER_SET, BINARY_SET), use regular PHP arrays:

```php
<?php
use ByJG\AnyDataset\NoSql\Enum\DynamoDbAttributeType;
use ByJG\AnyDataset\NoSql\Factory;

$options = [
    "KeyName" => "id",
    "Types" => [
        "id" => DynamoDbAttributeType::STRING,
        "tags" => DynamoDbAttributeType::STRING_SET,
        "ratings" => DynamoDbAttributeType::NUMBER_SET
    ]
];

$dynamodb = Factory::getInstance('dynamodb://....');
$dynamodb->put(
    'post123',
    [
        "tags" => ["php", "aws", "dynamodb"],  // String set
        "ratings" => [4, 5, 3, 5]              // Number set
    ],
    $options
);
```

The DynamoDB driver in this library handles the conversion to and from DynamoDB attribute format automatically, making it easier to work with complex types.

## Further Reading

- [AWS SDK for PHP - DynamoDB Documentation](https://docs.aws.amazon.com/aws-sdk-php/v2/guide/service-dynamodb.html)
- [DynamoDB Client API Reference](https://docs.aws.amazon.com/aws-sdk-php/v2/api/class-Aws.DynamoDb.DynamoDbClient.html)

----
[Open source ByJG](http://opensource.byjg.com)
