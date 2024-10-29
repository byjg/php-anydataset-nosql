<?php

namespace Tests;

use ByJG\AnyDataset\Core\Enum\Relation;
use ByJG\AnyDataset\Core\IteratorFilter;
use ByJG\AnyDataset\NoSql\Factory;
use ByJG\AnyDataset\NoSql\MongoDbDriver;
use ByJG\AnyDataset\NoSql\NoSqlDocument;
use PHPUnit\Framework\TestCase;

class MongoDbDriverTest extends TestCase
{
    /**
     * @var MongoDbDriver
     */
    protected $dbDriver;
    
    const TEST_COLLECTION = 'collectionTest';

    public function setUp(): void
    {
        $mongodbConnection = getenv("MONGODB_CONNECTION");

        if (empty($mongodbConnection)) {
            return;
        }

        $this->dbDriver = Factory::getInstance($mongodbConnection);

        $this->dbDriver->save(
            new NoSqlDocument(
                null,
                self::TEST_COLLECTION,
                new Document('Hilux', 'Toyota', 120000)
            )
        );
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
    
    public function tearDown(): void
    {
        if (!empty($this->dbDriver)) {
            $filter = new IteratorFilter();
            $this->dbDriver->deleteDocuments($filter, self::TEST_COLLECTION);
        }
    }

    /**
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function testSaveDocument()
    {
        if (empty($this->dbDriver)) {
            $this->markTestIncomplete("In order to test MongoDB you must define MONGODB_CONNECTION");
        }

        // Get the Object to test
        $filter = new IteratorFilter();
        $filter->and('name', Relation::EQUAL, 'Hilux');
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
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function testSaveDocumentEntity()
    {
        if (empty($this->dbDriver)) {
            $this->markTestIncomplete("In order to test MongoDB you must define MONGODB_CONNECTION");
        }

        // Get the Object to test
        $filter = new IteratorFilter();
        $filter->and('name', Relation::EQUAL, 'Hilux');
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
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function testDelete()
    {
        if (empty($this->dbDriver)) {
            $this->markTestIncomplete("In order to test MongoDB you must define MONGODB_CONNECTION");
        }

        // Get the Object to test
        $filter = new IteratorFilter();
        $filter->and('name', Relation::EQUAL, 'Uno');
        $document = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(1, $document);

        // Delete
        $this->dbDriver->deleteDocuments($filter, self::TEST_COLLECTION);

        // Check if object do not exist
        $document = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertEmpty($document);

        // Check if other objects weren't deleted
        $filter = new IteratorFilter();
        $filter->and('name', Relation::EQUAL, 'Hilux');
        $document = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(1, $document);
    }

    /**
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function testGetDocuments()
    {
        if (empty($this->dbDriver)) {
            $this->markTestIncomplete("In order to test MongoDB you must define MONGODB_CONNECTION");
        }

        $filter = new IteratorFilter();
        $filter->and('price', Relation::LESS_OR_EQUAL_THAN, 40000);
        $documents = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(2, $documents);
        $this->assertEquals('Fox', $documents[0]->getDocument()['name']);
        $this->assertEquals('Volkswagen', $documents[0]->getDocument()['brand']);
        $this->assertEquals('40000', $documents[0]->getDocument()['price']);
        $this->assertEquals('Uno', $documents[1]->getDocument()['name']);
        $this->assertEquals('Fiat', $documents[1]->getDocument()['brand']);
        $this->assertEquals('35000', $documents[1]->getDocument()['price']);

        $filter = new IteratorFilter();
        $filter->and('price', Relation::LESS_THAN, 40000);
        $documents = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(1, $documents);
        $this->assertEquals('Uno', $documents[0]->getDocument()['name']);
        $this->assertEquals('Fiat', $documents[0]->getDocument()['brand']);
        $this->assertEquals('35000', $documents[0]->getDocument()['price']);

        $filter = new IteratorFilter();
        $filter->and('price', Relation::GREATER_OR_EQUAL_THAN, 90000);
        $documents = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(2, $documents);
        $this->assertEquals('Hilux', $documents[0]->getDocument()['name']);
        $this->assertEquals('Toyota', $documents[0]->getDocument()['brand']);
        $this->assertEquals('120000', $documents[0]->getDocument()['price']);
        $this->assertEquals('A3', $documents[1]->getDocument()['name']);
        $this->assertEquals('Audi', $documents[1]->getDocument()['brand']);
        $this->assertEquals('90000', $documents[1]->getDocument()['price']);

        $filter = new IteratorFilter();
        $filter->and('price', Relation::GREATER_THAN, 90000);
        $documents = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(1, $documents);
        $this->assertEquals('Hilux', $documents[0]->getDocument()['name']);
        $this->assertEquals('Toyota', $documents[0]->getDocument()['brand']);
        $this->assertEquals('120000', $documents[0]->getDocument()['price']);

        $filter = new IteratorFilter();
        $filter->and('name', Relation::STARTS_WITH, 'Co');
        $documents = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(2, $documents);
        $this->assertEquals('Corolla', $documents[0]->getDocument()['name']);
        $this->assertEquals('Toyota', $documents[0]->getDocument()['brand']);
        $this->assertEquals('80000', $documents[0]->getDocument()['price']);
        $this->assertEquals('Cobalt', $documents[1]->getDocument()['name']);
        $this->assertEquals('Chevrolet', $documents[1]->getDocument()['brand']);
        $this->assertEquals('60000', $documents[1]->getDocument()['price']);

        $filter = new IteratorFilter();
        $filter->and('name', Relation::CONTAINS, 'oba');
        $documents = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(1, $documents);
        $this->assertEquals('Cobalt', $documents[0]->getDocument()['name']);
        $this->assertEquals('Chevrolet', $documents[0]->getDocument()['brand']);
        $this->assertEquals('60000', $documents[0]->getDocument()['price']);
    }

    /**
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function testBulkUpdate()
    {
        if (empty($this->dbDriver)) {
            $this->markTestIncomplete("In order to test MongoDB you must define MONGODB_CONNECTION");
        }

        // Get the Object to test
        $filter = new IteratorFilter();
        $filter->and('brand', Relation::IN, ['Toyota', 'Audi']);
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
        $filter->and('name', Relation::EQUAL, 'Hilux');
        $documentList = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertCount(1, $documentList);
    }
}
