<?php

namespace Tests;

use ByJG\AnyDataset\NoSql\AwsDynamoDbDriver;
use ByJG\AnyDataset\NoSql\Enum\DynamoDbAttributeType;
use ByJG\AnyDataset\NoSql\Factory;
use PHPUnit\Framework\TestCase;

class DynamoDbAttributeTypeTest extends TestCase
{
    /**
     * @var AwsDynamoDbDriver|null
     */
    protected ?AwsDynamoDbDriver $object = null;
    
    /**
     * @var string
     */
    protected string $testTable = 'test-attributes';
    
    #[\Override]
    protected function setUp(): void
    {
        $awsConnection = getenv("DYNAMODB_CONNECTION");
        if (empty($awsConnection)) {
            $this->markTestSkipped('No DynamoDB connection string available');
        }
        
        /** @psalm-suppress InvalidPropertyAssignmentValue */
        $this->object = Factory::getInstance($awsConnection);
        
        try {
            $this->ensureTableExists();
        } catch (\Exception $e) {
            $this->markTestSkipped("Could not create test table: " . $e->getMessage());
        }
    }
    
    protected function ensureTableExists()
    {
        // No generic table creation, each test method will create its own table
    }
    
    #[\Override]
    protected function tearDown(): void
    {
        if (!empty($this->object)) {
            // Don't try to clean up test records as it will just slow down tests
            $this->object = null;
        }
    }
    
    public function testBasicAttributeTypes()
    {
        if (empty($this->object)) {
            $this->markTestSkipped('DynamoDB driver not available');
        }
        
        // Create a unique table name for this test
        $testTable = 'test-basic-attributes';
        $testId = 1001;
        
        try {
            // Create the table specifically for this test
            try {
                $this->object->client()->describeTable(['TableName' => $testTable]);
            } catch (\Exception $ex) {
                $this->object->client()->createTable([
                    'TableName' => $testTable,
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
                    'TableName' => $testTable
                ]);
            }
            
            // Define a simple test record with basic types
            $testData = [
                'stringValue' => 'This is a string',
                'numberValue' => 42
            ];
            
            // Define the type mapping for our test record using the enum
            $options = [
                'KeyName' => 'id', // This must match the key in the table schema
                'Types' => [
                    'id' => DynamoDbAttributeType::NUMBER, // Must match the attribute type in table schema
                    'stringValue' => DynamoDbAttributeType::STRING,
                    'numberValue' => DynamoDbAttributeType::NUMBER
                ],
                'TableName' => $testTable
            ];
            
            // Put the test data into DynamoDB
            $this->object->put($testId, $testData, $options);
            
            // Retrieve the data from DynamoDB
            $retrievedData = $this->object->get($testId, $options);
            
            // Verify data was retrieved
            $this->assertNotNull($retrievedData, 'Failed to retrieve data from DynamoDB');
            
            // Test ID value
            $this->assertEquals($testId, $retrievedData['id'], 'ID mismatch');
            
            // Test string type handling
            $this->assertEquals('This is a string', $retrievedData['stringValue'], 'String value mismatch');
            
            // Test number type handling
            $this->assertEquals(42, $retrievedData['numberValue'], 'Number value mismatch');
            
            // Cleanup - remove the test record
            $this->object->remove($testId, $options);
            
            // Verify record was removed
            $this->assertFalse($this->object->has($testId, $options), 'Failed to remove test record');
            
            // Clean up the table
            try {
                $this->object->client()->deleteTable(['TableName' => $testTable]);
            } catch (\Exception $e) {
                // Ignore cleanup errors
            }
            
        } catch (\Exception $e) {
            $this->fail('Exception thrown during test: ' . $e->getMessage());
        }
    }
    
    public function testSimpleQuery()
    {
        if (empty($this->object)) {
            $this->markTestSkipped('DynamoDB driver not available');
        }
        
        // Create a unique table name for this test
        $testTable = 'test-query';
        $testPrefix = 2000;
        
        try {
            // Create the table specifically for this test
            try {
                $this->object->client()->describeTable(['TableName' => $testTable]);
            } catch (\Exception $ex) {
                $this->object->client()->createTable([
                    'TableName' => $testTable,
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
                    'TableName' => $testTable
                ]);
            }
            
            // Define test record IDs
            $recordId1 = $testPrefix + 1;
            $recordId2 = $testPrefix + 2;
            
            // Define the type mapping for our test records
            $options = [
                'KeyName' => 'id', // This must match the key in the table schema
                'Types' => [
                    'id' => DynamoDbAttributeType::NUMBER, // Must match the attribute type in table schema
                    'category' => DynamoDbAttributeType::STRING,
                    'count' => DynamoDbAttributeType::NUMBER
                ],
                'TableName' => $testTable
            ];
            
            // Insert test records with book category
            $this->object->put($recordId1, [
                'category' => 'books',
                'count' => 10
            ], $options);
            
            // Insert test records with electronics category
            $this->object->put($recordId2, [
                'category' => 'electronics',
                'count' => 5
            ], $options);
            
            // Test a scan operation with string filter
            $scanOptions = [
                'TableName' => $testTable,
                'ScanFilter' => [
                    'category' => [
                        'AttributeValueList' => [
                            [DynamoDbAttributeType::STRING->value => 'books']
                        ],
                        'ComparisonOperator' => 'EQ'
                    ]
                ],
                'Types' => $options['Types']
            ];
            
            $results = $this->object->getIterator($scanOptions);
            $resultsArray = $results->toArray();
            
            // Should find at least 1 record with category 'books'
            $booksCount = 0;
            foreach ($resultsArray as $item) {
                if (isset($item['category']) && $item['category'] === 'books' && 
                    isset($item['id']) && $item['id'] >= $testPrefix && $item['id'] < ($testPrefix + 10)) {
                    $booksCount++;
                }
            }
            
            $this->assertGreaterThanOrEqual(1, $booksCount, 'String filter did not return the expected number of records');
            
            // Cleanup - remove all test records
            $this->object->remove($recordId1, $options);
            $this->object->remove($recordId2, $options);
            
            // Clean up the table
            try {
                $this->object->client()->deleteTable(['TableName' => $testTable]);
            } catch (\Exception $e) {
                // Ignore cleanup errors
            }
            
        } catch (\Exception $e) {
            $this->fail('Exception thrown during query test: ' . $e->getMessage());
        }
    }
    
    public function testComplexAttributeTypes()
    {
        if (empty($this->object)) {
            $this->markTestSkipped('DynamoDB driver not available');
        }
        
        // Create a unique table name for this test
        $testTable = 'test-complex';
        $testId = 3001;
        
        try {
            // Create the table specifically for this test
            try {
                $this->object->client()->describeTable(['TableName' => $testTable]);
            } catch (\Exception $ex) {
                $this->object->client()->createTable([
                    'TableName' => $testTable,
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
                    'TableName' => $testTable
                ]);
            }
            
            // Define test data with properly formatted values
            $testData = [
                'boolValue' => true,
                'nullValue' => null,
                'listValue' => ['item1', 'item2', 'item3'],
                'mapValue' => [
                    'key1' => 'value1',
                    'key2' => 'value2'
                ],
                'stringSetValue' => ['tag1', 'tag2', 'tag3'],
                'numberSetValue' => [1, 2, 3]
            ];
            
            // Define the type mapping using the enum
            $options = [
                'KeyName' => 'id', // This must match the key in the table schema
                'Types' => [
                    'id' => DynamoDbAttributeType::NUMBER, // Must match the attribute type in table schema
                    'boolValue' => DynamoDbAttributeType::BOOLEAN,
                    'nullValue' => DynamoDbAttributeType::NULL,
                    'listValue' => DynamoDbAttributeType::LIST,
                    'mapValue' => DynamoDbAttributeType::MAP,
                    'stringSetValue' => DynamoDbAttributeType::STRING_SET,
                    'numberSetValue' => DynamoDbAttributeType::NUMBER_SET
                ],
                'TableName' => $testTable
            ];
            
            // Put the test data into DynamoDB
            $this->object->put($testId, $testData, $options);
            
            // Retrieve the data from DynamoDB
            $retrievedData = $this->object->get($testId, $options);
            
            // Verify data was retrieved
            $this->assertNotNull($retrievedData, 'Failed to retrieve data from DynamoDB');
            
            // Verify basic types
            $this->assertEquals($testId, $retrievedData['id'], 'ID mismatch');
            $this->assertTrue(isset($retrievedData['boolValue']), 'Boolean value not found');
            $this->assertNull($retrievedData['nullValue'], 'Null value mismatch');
            
            // For complex types, verify we have the right structure
            $this->assertArrayHasKey('listValue', $retrievedData, 'List value not found');
            $this->assertIsArray($retrievedData['listValue'], 'List should be an array');
            $this->assertCount(3, $retrievedData['listValue'], 'List should have 3 items');
            
            $this->assertArrayHasKey('mapValue', $retrievedData, 'Map value not found');
            $this->assertIsArray($retrievedData['mapValue'], 'Map should be an array');
            $this->assertArrayHasKey('key1', $retrievedData['mapValue'], 'Map should have key1');
            
            $this->assertArrayHasKey('stringSetValue', $retrievedData, 'String set not found');
            $this->assertIsArray($retrievedData['stringSetValue'], 'String set should be an array');
            
            $this->assertArrayHasKey('numberSetValue', $retrievedData, 'Number set not found');
            $this->assertIsArray($retrievedData['numberSetValue'], 'Number set should be an array');
            
            // Cleanup
            $this->object->remove($testId, $options);
            
            // Clean up the table
            try {
                $this->object->client()->deleteTable(['TableName' => $testTable]);
            } catch (\Exception $e) {
                // Ignore cleanup errors
            }
            
        } catch (\Exception $e) {
            $this->fail('Exception thrown during test: ' . $e->getMessage());
        }
    }
} 