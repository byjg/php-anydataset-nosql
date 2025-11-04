<?php

namespace Tests;

use ByJG\AnyDataset\NoSql\AwsDynamoDbDriver;
use ByJG\AnyDataset\NoSql\Enum\DynamoDbAttributeType;
use ByJG\AnyDataset\NoSql\Factory;
use Override;
use PHPUnit\Framework\TestCase;

class AwsDynamoDbDriverTest extends TestCase
{
    /**
     * @var AwsDynamoDbDriver|null
     */
    protected AwsDynamoDbDriver|null $object = null;

    /**
     * @var string
     */
    protected string $testTable = 'dynamodb-test';
    
    #[Override]
    protected function setUp(): void
    {
        $awsConnection = getenv("DYNAMODB_CONNECTION");
        if (empty($awsConnection)) {
            $this->markTestSkipped("In order to test DynamoDB you must define DYNAMODB_CONNECTION");
        }

        /** @psalm-suppress InvalidPropertyAssignmentValue */
        $this->object = Factory::getInstance($awsConnection);
        
        $this->ensureTableExists();
    }

    protected function ensureTableExists()
    {
        $this->object->client()->createTable([
            'TableName' => $this->testTable,
            'KeySchema' => [
                [
                    'AttributeName' => 'id',
                    'KeyType' => 'HASH'
                ]
            ],
            'AttributeDefinitions' => [
                [
                    'AttributeName' => 'id',
                    'AttributeType' => DynamoDbAttributeType::NUMBER->value
                ],
            ],
            'ProvisionedThroughput' => [
                'ReadCapacityUnits' => 5,
                'WriteCapacityUnits' => 5
            ]
        ]);

        // Wait for table to be created
        $this->object->client()->waitUntil('TableExists', [
            'TableName' => $this->testTable
        ]);
    }

    #[Override]
    protected function tearDown(): void
    {
        // No cleanup needed for this test
        $this->object->client()->deleteTable(['TableName' => $this->testTable]);
        $this->object = null;
    }

    /**
     */
    public function testDynamoDbOperations()
    {
        if (empty($this->object)) {
            $this->markTestSkipped("In order to test DynamoDB you must define DYNAMODB_CONNECTION");
        }

        // Define options with table name and key schema
        $options = [
            'KeyName' => 'id',
            'TableName' => $this->testTable,
            'Types' => [
                'id' => DynamoDbAttributeType::NUMBER,
                'Name' => DynamoDbAttributeType::STRING,
                'SurName' => DynamoDbAttributeType::STRING,
                'Active' => DynamoDbAttributeType::BOOLEAN,
                "Tags" => DynamoDbAttributeType::STRING_SET,
                "CatIds" => DynamoDbAttributeType::NUMBER_SET,
                "details" => DynamoDbAttributeType::MAP,
            ]
        ];

        // Test has() method on non-existent keys
        $this->assertFalse($this->object->has(1, $options));
        $this->assertFalse($this->object->has(2, $options));

        // Add records
        $this->object->put(
            1,
            [
                "Name" => "John",
                "SurName" => "Doe",
                "Active" => true,
                "Tags" => ["tag1", "tag2"],
                "CatIds" => [1, 2],
                "details" => [
                    "name" => "Product Name",
                    "price" => 99.99,
                    "inStock" => true
                ]
            ],
            $options
        );
        $this->object->put(
            2,
            [
                "Name" => "Jane",
                "SurName" => "Smith",
                "Active" => false,
                "Tags" => ["tag3", "tag4"],
                "CatIds" => [3, 4],
                "details" => [
                    "name" => "Other Product",
                    "price" => 250,
                    "inStock" => false
                ]
            ],
            $options
        );

        // Test has() method on existing keys
        $this->assertTrue($this->object->has(1, $options));
        $this->assertTrue($this->object->has(2, $options));

        // Test get() method
        $elem1 = $this->object->get(1, $options);
        $this->assertNotNull($elem1, "Failed to retrieve record with ID test1");
        $this->assertEquals([
            "Name" => "John",
            "SurName" => "Doe",
            "Active" => true,
            "Tags" => ["tag1", "tag2"],
            "CatIds" => [1, 2],
            "details" => [
                "name" => "Product Name",
                "price" => 99.99,
                "inStock" => true
            ],
            "id" => 1
        ], $elem1);

        $elem2 = $this->object->get(2, $options);
        $this->assertNotNull($elem2, "Failed to retrieve record with ID test2");
        $this->assertEquals([
            "Name" => "Jane",
            "SurName" => "Smith",
            "Active" => false,
            "Tags" => ["tag3", "tag4"],
            "CatIds" => [3, 4],
            "details" => [
                "name" => "Other Product",
                "price" => 250,
                "inStock" => false
            ],
            "id" => 2
        ], $elem2);

        // Test remove() method
        $this->object->remove(1, $options);
        $this->object->remove(2, $options);

        // Verify records were removed
        $this->assertFalse($this->object->has(1, $options), "Record with ID test1 was not removed");
        $this->assertFalse($this->object->has(2, $options), "Record with ID test2 was not removed");
    }
}
