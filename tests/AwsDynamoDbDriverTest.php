<?php

namespace TestsDb\AnyDataset;

use Aws\DynamoDb\Exception\DynamoDbException;
use ByJG\AnyDataset\NoSql\AwsDynamoDbDriver;
use ByJG\AnyDataset\NoSql\Factory;
use PHPUnit\Framework\TestCase;

class AwsDynamoDbDriverTest extends TestCase
{
    /**
     * @var AwsDynamoDbDriver
     */
    protected $object;

    protected $options =
        [
            "KeyName" => "key",
            "Types" =>
                [
                    "key" => "N"
                ]
        ];

    protected $scanOptions =
        [
            "ScanFilter" => []
        ];

    protected $queryOptions =
        [
            "KeyConditions" => [
                "key" => [
                    "AttributeValueList" => [
                        ["N" => "1"]
                    ],
                    "ComparisonOperator" => "EQ"
                ]
            ]
        ];

    protected function setUp()
    {
        $awsConnection = getenv("DYNAMODB_CONNECTION");
        if (!empty($awsConnection)) {
            $this->object = Factory::getKeyValueInstance($awsConnection);

            $this->createTable();

            $this->object->remove(1, $this->options);
            $this->object->remove(2, $this->options);
        }
    }

    protected function createTable()
    {
        try {
            $this->object->client()->describeTable(['TableName' => $this->object->getTablename()]);
        } catch (DynamoDbException $ex) {
            // table doesn't exist, create it below
            $this->object->client()->createTable([
                'TableName' => $this->object->getTablename(),
                'KeySchema' => [
                    [
                        'AttributeName' => 'key',
                        'KeyType' => 'HASH'
                    ]
                ],
                'AttributeDefinitions' => [
                    [
                        'AttributeName' => 'key',
                        'AttributeType' => 'N'
                    ],
                ],
                'ProvisionedThroughput' => [
                    'ReadCapacityUnits' => 10,
                    'WriteCapacityUnits' => 10
                ]
            ]);
        }
    }

    protected function tearDown()
    {
        if (!empty($this->object)) {
            $this->object->remove(1, $this->options);
            $this->object->remove(2, $this->options);
            $this->object = null;
        }
    }

    public function testDynamoDbOperations()
    {
        if (empty($this->object)) {
            $this->markTestIncomplete("In order to test DynamoDB you must define DYNAMODB_CONNECTION");
        }

        // Get current bucket
        $iterator = $this->object->getIterator($this->queryOptions);
        $this->assertEquals(0, $iterator->count());

        // Add an element
        $this->object->put(1, ["Name" => "John", "SurName" => "Doe"], $this->options);
        $this->object->put(2, ["Name" => "Jane", "SurName" => "Smith"], $this->options);

        // Check new elements
        $iterator = $this->object->getIterator($this->queryOptions);
        $this->assertEquals(1, $iterator->count());
        $this->assertEquals(
            [
                [
                    "name" => "John",
                    "surname" => "Doe",
                    "key" => 1,
                    '__id' => 0,
                    '__key' => 0
                ]
            ],
            $iterator->toArray()
        );

        // Get elements
        $elem1 = $this->object->get(1, $this->options);
        $this->assertEquals([
            "Name" => "John",
            "SurName" => "Doe",
            "key" => 1
        ], $elem1);
        $elem2 = $this->object->get(2, $this->options);
        $this->assertEquals([
            "Name" => "Jane",
            "SurName" => "Smith",
            "key" => 2
        ], $elem2);

        // Remove elements
        $this->object->remove(1, $this->options);
        $this->object->remove(2, $this->options);

        // Check new elements
        $iterator = $this->object->getIterator($this->queryOptions);
        $this->assertEquals(0, $iterator->count());
    }

//    public function testGetChunk()
//    {
//        if (empty($this->object)) {
//            $this->markTestIncomplete("In order to test DynamoDB you must define DYNAMODB_CONNECTION");
//        }
//
//        $this->object->put(
//            "KEY",
//            str_repeat("0", 256) . str_repeat("1", 256) . str_repeat("2", 250)
//        );
//
//        $part1 = $this->object->getChunk("KEY", [], 256, 0);
//        $part2 = $this->object->getChunk("KEY", [], 256, 1);
//        $part3 = $this->object->getChunk("KEY", [], 256, 2);
//
//        $this->assertEquals(str_repeat("0", 256), $part1);
//        $this->assertEquals(str_repeat("1", 256), $part2);
//        $this->assertEquals(str_repeat("2", 250), $part3);
//    }
}
