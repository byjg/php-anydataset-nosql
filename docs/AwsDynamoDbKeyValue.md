# AWS DynamoDB

```php
<?php
$dynamodb = \ByJG\AnyDataset\NoSql\Factory::getKeyValueInstance('dynamodb://access_key:secret_key@region/tablename');
```

The full connection string can be:

```
dynamodb://AKA12345678899:aaaaaaaaaaaaaaaaaaaaaaaaa@us-east-1/mytable
```

You can add any extra arguments supported by the DynamoDB api. You can get a full list here:
 - https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.AwsClient.html#___construct

One of the most populars is the parameter `endpoint` where we can set a custom endpoint to access 
an DynamoDB compatible interface. 

An example can be: 

```
s3://AKA12345678899:aaaaaaaaaaaaaaaaaaaaaaaaa@us-east-1/tablename?endpoint=http://localhost:8000
```


## Preparing to use DynamoDb

DynamoDb stores the information slightly different than a model dto structure.

Here an example how DynamoDb requires a model:

```
[
    'id'      => ['N' => '1201'],
    'time'    => ['N' => $time],
    'error'   => ['S' => 'Executive overflow'],
    'message' => ['S' => 'no vacant areas']
]
```

and a definition more usual is to have :

```
[
    'id'      => 1201,
    'time'    => $time,
    'error'   => 'Executive overflow',
    'message' => 'no vacant areas'
]
```

We will use the second definition. However, every put/get/remove method we will need to setup 
a list of options to define this data model. 

basically we have to create an array with 2 keys:
- KeyName: Contains the key hame (Currently supports table with only one key)
- Types: Defines the field names and type

Example:

```php
<?php

$options = [
    "KeyName" => "id",
    "Types" => [
        "id" => "N",
        "time" => "N"
    ]
];
```

The examples below will use this definition.

### Inserting/Updating data

```php
<?php
$dynamodb = \ByJG\AnyDataset\NoSql\Factory::getKeyValueInstance('dynamodb://....');
$dynamodb->put(
    1201,
    [
        "time" => 1234567899,
        "error" => 'Executive overflow',
        "Message" => "No Vacant Areas"
    ],
    $options  // See above
);
```

### Retrieve a value

```php
<?php
$dynamodb = \ByJG\AnyDataset\NoSql\Factory::getKeyValueInstance('dynamodb://....');
$value = $dynamodb->get(1201, $options);

/* Should Return:
[
    'id'      => 1201,
    'time'    => $time,
    'error'   => 'Executive overflow',
    'message' => 'no vacant areas'
]
*/
```

### Remove a value

```php
<?php
$dynamodb = \ByJG\AnyDataset\NoSql\Factory::getKeyValueInstance('dynamodb://....');
$dynamodb->remove(1201);
```


## Listing objects

To get a list of the objects you need to pass an array of options with the keys `KeyConditions` or `ScanFilter`.

Example:

```php
<?php
$dynamodb = \ByJG\AnyDataset\NoSql\Factory::getKeyValueInstance('dynamodb://....');

$options = [
   "KeyConditions" => [
       "id" => [
           "AttributeValueList" => [
               ["N" => "1201"]
           ],
           "ComparisonOperator" => "EQ"
       ]
   ]
];

$iterator = $dynamodb->getIterator($options);
print_r($iterator->toArray());
```

## Further reading

- [https://docs.aws.amazon.com/aws-sdk-php/v2/guide/service-dynamodb.html]()
- [https://docs.aws.amazon.com/aws-sdk-php/v2/api/class-Aws.DynamoDb.DynamoDbClient.html]()

----
[Open source ByJG](http://opensource.byjg.com)