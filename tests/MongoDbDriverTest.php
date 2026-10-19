<?php

namespace Tests;

use ByJG\AnyDataset\Core\Enum\Relation;
use ByJG\AnyDataset\Core\IteratorFilter;
use ByJG\AnyDataset\NoSql\Factory;
use ByJG\AnyDataset\NoSql\MongoDbDriver;
use ByJG\AnyDataset\NoSql\NoSqlDocument;
use ByJG\Util\Uri;
use MongoDB\Driver\Command;
use MongoDB\Driver\Exception\ConnectionException;
use MongoDB\Driver\Exception\Exception;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MongoDbDriverTest extends TestCase
{
    /**
     * @var MongoDbDriver
     */
    protected $dbDriver;
    
    const TEST_COLLECTION = 'collectionTest';

    #[Override]
    public function setUp(): void
    {
        $mongodbConnection = getenv("MONGODB_CONNECTION");

        if (empty($mongodbConnection)) {
            return;
        }

        $this->dbDriver = Factory::getInstance($mongodbConnection);

        try {
            $this->dbDriver->save(
                new NoSqlDocument(
                    null,
                    self::TEST_COLLECTION,
                    new Document('Hilux', 'Toyota', 120000)
                )
            );
        } catch (ConnectionException $e) {
            // Only an unreachable server (or bad credentials) skips the tests. Any
            // other error is a real failure: a catch-all here hid a broken fixture
            // and kept these tests from running at all.
            $this->markTestIncomplete("MongoDB is not reachable. Run docker compose and set MONGODB_CONNECTION. Error: " . $e->getMessage());
            return;
        }
        $this->dbDriver->save(
            new NoSqlDocument(
                null,
                self::TEST_COLLECTION,
                ['name' => 'A3', 'brand' => 'Audi', 'price' => 90000]
            )
        );
        $this->dbDriver->save(
            new NoSqlDocument(
                null,
                self::TEST_COLLECTION,
                ['name' => 'Fox', 'brand' => 'Volkswagen', 'price' => 40000]
            )
        );
        $this->dbDriver->save(
            new NoSqlDocument(
                null,
                self::TEST_COLLECTION,
                ['name' => 'Corolla', 'brand' => 'Toyota', 'price' => 80000]
            )
        );
        $this->dbDriver->save(
            new NoSqlDocument(
                null,
                self::TEST_COLLECTION,
                ['name' => 'Cobalt', 'brand' => 'Chevrolet', 'price' => 60000]
            )
        );
        $this->dbDriver->save(
            new NoSqlDocument(
                null,
                self::TEST_COLLECTION,
                ['name' => 'Uno', 'brand' => 'Fiat', 'price' =>35000]
            )
        );
    }
    
    #[Override]
    public function tearDown(): void
    {
        if (!empty($this->dbDriver)) {
            $filter = new IteratorFilter();
            $this->dbDriver->deleteDocuments($filter, self::TEST_COLLECTION);
        }
    }

    /**
     * @throws Exception
     */
    public function testSaveDocument()
    {
        if (empty($this->dbDriver)) {
            $this->markTestIncomplete("In order to test MongoDB you must define MONGODB_CONNECTION");
        }

        // Get the Object to test
        $filter = new IteratorFilter();
        $filter->and('name', Relation::Equal, 'Hilux');
        $document = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);

        // Check if returns one document
        $this->assertCount(1, $document);

        // Check if the default fields are here
        $data = $document[0]->getDocument();
        $this->assertNotEmpty($data['_id']);
        $this->assertNotEmpty($data['createdAt']);
        $this->assertNotEmpty($data['updatedAt']);
        $this->assertEquals($data['createdAt']->toDatetime(), $data['updatedAt']->toDatetime());
        unset($data['_id']);
        unset($data['createdAt']);
        unset($data['updatedAt']);

        // Check if the context is the expected
        $this->assertEquals(
            ['name' => 'Hilux', 'brand' => 'Toyota', 'price' => 120000],
            $data
        );

        // Create a new document with a partial field to update
        $documentToUpdate = new NoSqlDocument(
            $document[0]->getIdDocument(),
            self::TEST_COLLECTION,
            [ 'price' => 150000 ]
        );
        sleep(1); // Just to force a new Update DateTime
        $documentSaved = $this->dbDriver->save($documentToUpdate);

        // Get the saved document
        $documentFromDb = $this->dbDriver->getDocumentById($document[0]->getIdDocument(), self::TEST_COLLECTION);

        // Check if the document have the same ID (Update) and Have the updatedAt data
        $data = $documentFromDb->getDocument();
        $this->assertEquals($documentSaved->getIdDocument(), $document[0]->getIdDocument());
        $this->assertEquals($data['_id'], $document[0]->getIdDocument());
        $this->assertNotEmpty($data['createdAt']);
        $this->assertNotEmpty($data['updatedAt']);
        $this->assertNotEquals($data['createdAt']->toDatetime(), $data['updatedAt']->toDatetime());
        unset($data['_id']);
        unset($data['createdAt']);
        unset($data['updatedAt']);
        $this->assertEquals(
            ['name' => 'Hilux', 'brand' => 'Toyota', 'price' => 150000],
            $data
        );
    }

    /**
     * @throws Exception
     */
    public function testSaveDocumentEntity()
    {
        if (empty($this->dbDriver)) {
            $this->markTestIncomplete("In order to test MongoDB you must define MONGODB_CONNECTION");
        }

        // Get the Object to test
        $filter = new IteratorFilter();
        $filter->and('name', Relation::Equal, 'Hilux');
        $document = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);

        // Check if returns one document
        $this->assertCount(1, $document);

        // Check if the default fields are here
        $entity = $document[0]->getDocument(Document::class);
        $this->assertInstanceOf(Document::class, $entity);
        $this->assertNotEmpty($entity->get_id());
        $this->assertNotEmpty($entity->getCreatedAt());
        $this->assertNotEmpty($entity->getUpdatedAt());
        $this->assertEquals($entity->getCreatedAt()->toDatetime(), $entity->getCreatedAt()->toDatetime());

        // Check if the context is the expected
        $this->assertEquals(
            ['Hilux', 'Toyota', 120000],
            [$entity->getName(), $entity->getBrand(), $entity->getPrice()]
        );

        $entity->setPrice(150000);

        // Create a new document with a partial field to update
        $documentToUpdate = new NoSqlDocument(
            $document[0]->getIdDocument(),
            self::TEST_COLLECTION,
            $entity
        );
        sleep(1); // Just to force a new Update DateTime
        $documentSaved = $this->dbDriver->save($documentToUpdate);

        // Get the saved document
        $documentFromDb = $this->dbDriver->getDocumentById($document[0]->getIdDocument(), self::TEST_COLLECTION);

        // Check if the document have the same ID (Update) and Have the updatedAt data
        $updEntity = $documentFromDb->getDocument(Document::class);
        $this->assertEquals($documentSaved->getIdDocument(), $document[0]->getIdDocument());
        $this->assertEquals($updEntity->get_id(), $document[0]->getIdDocument());
        $this->assertNotEmpty($updEntity->getCreatedAt());
        $this->assertNotEmpty($updEntity->getUpdatedAt());
        $this->assertEquals($updEntity->getCreatedAt()->toDatetime(), $updEntity->getCreatedAt()->toDatetime());
        $this->assertEquals(
            ['Hilux', 'Toyota', 150000],
            [$updEntity->getName(), $updEntity->getBrand(), $updEntity->getPrice()]
        );
    }

    /**
     * @throws Exception
     */
    public function testDelete()
    {
        if (empty($this->dbDriver)) {
            $this->markTestIncomplete("In order to test MongoDB you must define MONGODB_CONNECTION");
        }

        // Get the Object to test
        $filter = new IteratorFilter();
        $filter->and('name', Relation::Equal, 'Uno');
        $document = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(1, $document);

        // Delete
        $this->dbDriver->deleteDocuments($filter, self::TEST_COLLECTION);

        // Check if object do not exist
        $document = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertEmpty($document);

        // Check if other objects weren't deleted
        $filter = new IteratorFilter();
        $filter->and('name', Relation::Equal, 'Hilux');
        $document = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(1, $document);
    }

    /**
     * @throws Exception
     */
    public function testGetDocuments()
    {
        if (empty($this->dbDriver)) {
            $this->markTestIncomplete("In order to test MongoDB you must define MONGODB_CONNECTION");
        }

        $filter = new IteratorFilter();
        $filter->and('price', Relation::LessThanOrEqualTo, 40000);
        $documents = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(2, $documents);
        $this->assertEquals('Fox', $documents[0]->getDocument()['name']);
        $this->assertEquals('Volkswagen', $documents[0]->getDocument()['brand']);
        $this->assertEquals('40000', $documents[0]->getDocument()['price']);
        $this->assertEquals('Uno', $documents[1]->getDocument()['name']);
        $this->assertEquals('Fiat', $documents[1]->getDocument()['brand']);
        $this->assertEquals('35000', $documents[1]->getDocument()['price']);

        $filter = new IteratorFilter();
        $filter->and('price', Relation::LessThan, 40000);
        $documents = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(1, $documents);
        $this->assertEquals('Uno', $documents[0]->getDocument()['name']);
        $this->assertEquals('Fiat', $documents[0]->getDocument()['brand']);
        $this->assertEquals('35000', $documents[0]->getDocument()['price']);

        $filter = new IteratorFilter();
        $filter->and('price', Relation::GreaterThanOrEqualTo, 90000);
        $documents = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(2, $documents);
        $this->assertEquals('Hilux', $documents[0]->getDocument()['name']);
        $this->assertEquals('Toyota', $documents[0]->getDocument()['brand']);
        $this->assertEquals('120000', $documents[0]->getDocument()['price']);
        $this->assertEquals('A3', $documents[1]->getDocument()['name']);
        $this->assertEquals('Audi', $documents[1]->getDocument()['brand']);
        $this->assertEquals('90000', $documents[1]->getDocument()['price']);

        $filter = new IteratorFilter();
        $filter->and('price', Relation::GreaterThan, 90000);
        $documents = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(1, $documents);
        $this->assertEquals('Hilux', $documents[0]->getDocument()['name']);
        $this->assertEquals('Toyota', $documents[0]->getDocument()['brand']);
        $this->assertEquals('120000', $documents[0]->getDocument()['price']);

        $filter = new IteratorFilter();
        $filter->and('name', Relation::StartsWith, 'Co');
        $documents = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(2, $documents);
        $this->assertEquals('Corolla', $documents[0]->getDocument()['name']);
        $this->assertEquals('Toyota', $documents[0]->getDocument()['brand']);
        $this->assertEquals('80000', $documents[0]->getDocument()['price']);
        $this->assertEquals('Cobalt', $documents[1]->getDocument()['name']);
        $this->assertEquals('Chevrolet', $documents[1]->getDocument()['brand']);
        $this->assertEquals('60000', $documents[1]->getDocument()['price']);

        $filter = new IteratorFilter();
        $filter->and('name', Relation::Contains, 'oba');
        $documents = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(1, $documents);
        $this->assertEquals('Cobalt', $documents[0]->getDocument()['name']);
        $this->assertEquals('Chevrolet', $documents[0]->getDocument()['brand']);
        $this->assertEquals('60000', $documents[0]->getDocument()['price']);
    }

    /**
     * @throws Exception
     */
    public function testBulkUpdate()
    {
        if (empty($this->dbDriver)) {
            $this->markTestIncomplete("In order to test MongoDB you must define MONGODB_CONNECTION");
        }

        // Get the Object to test
        $filter = new IteratorFilter();
        $filter->and('brand', Relation::In, ['Toyota', 'Audi']);
        $documentList = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(3, $documentList);
        foreach ($documentList as $document) {
            $this->assertNotEquals('30000', $document->getDocument()['price']);
        }

        // Delete
        $this->dbDriver->updateDocuments($filter, ["price" => 30000], self::TEST_COLLECTION);

        // Check if object do not exist
        $documentList = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(3, $documentList);
        foreach ($documentList as $document) {
            $this->assertEquals('30000', $document->getDocument()['price']);
        }

        // Check if other objects weren't deleted
        $filter = new IteratorFilter();
        $filter->and('name', Relation::Equal, 'Hilux');
        $documentList = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(1, $documentList);
    }

    public static function credentialsProvider(): array
    {
        return [
            'special characters' => ['user@:/%?#', 'p@ss:/%?#word'],
            'password "0"' => ['user0', '0'],
            'user "0"' => ['0', 'pass0'],
        ];
    }

    #[DataProvider('credentialsProvider')]
    public function testAuthenticateWithCredentials(string $username, string $password)
    {
        if (empty($this->dbDriver)) {
            $this->markTestIncomplete("In order to test MongoDB you must define MONGODB_CONNECTION");
        }

        $admin = $this->dbDriver->getDbConnection();
        $admin->executeCommand('admin', new Command([
            'createUser' => $username,
            'pwd' => $password,
            'roles' => [['role' => 'readWrite', 'db' => 'test']],
        ]));

        try {
            $host = new Uri(getenv("MONGODB_CONNECTION"));
            $driver = Factory::getInstance(
                'mongodb://' . rawurlencode($username) . ':' . rawurlencode($password) . '@' . $host->getHost() . '/test'
            );

            $filter = new IteratorFilter();
            $filter->and('name', Relation::Equal, 'Hilux');
            $this->assertCount(1, $driver->getDocuments($filter, self::TEST_COLLECTION));
        } finally {
            $admin->executeCommand('admin', new Command(['dropUser' => $username]));
        }
    }
}
