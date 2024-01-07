<?php

namespace ByJG\AnyDataset\NoSql;

use ByJG\AnyDataset\Core\Enum\Relation;
use ByJG\AnyDataset\Core\IteratorFilter;
use ByJG\Serializer\SerializerObject;
use ByJG\Util\Uri;
use DateTime;
use InvalidArgumentException;
use MongoDB\BSON\Binary;
use MongoDB\BSON\Decimal128;
use MongoDB\BSON\Javascript;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Timestamp;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Exception\Exception;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\WriteConcern;

class MongoDbDriver implements NoSqlInterface, RegistrableInterface
{
    /**
     * @var array
     */
    private array $excludeMongoClass;

    /**
     *
     * @var Manager|null;
     */
    protected ?Manager $mongoManager = null;

    /**
     * Enter description here...
     *
     * @var Uri
     */
    protected Uri $connectionUri;

    protected string $database;

    /**
     * Creates a new MongoDB connection.
     *
     *  mongodb://username:password@host:port/database
     *
     * @param Uri $connUri
     */
    public function __construct(Uri $connUri)
    {
        $this->connectionUri = $connUri;
        
        $this->excludeMongoClass = [
            Binary::class,
            Decimal128::class,
            Javascript::class,
            ObjectID::class,
            Timestamp::class,
            UTCDateTime::class,
        ];

        $hosts = $this->connectionUri->getHost();
        $port = $this->connectionUri->getPort() == '' ? 27017 : $this->connectionUri->getPort();
        $path = preg_replace('~^/~', '', $this->connectionUri->getPath());
        $database = $path;
        $username = $this->connectionUri->getUsername();
        $password = $this->connectionUri->getPassword();

        if ($username != '' && $password != '') {
            $auth = array('username' => $username, 'password' => $password, 'connect' => 'true');
        } else {
            $auth = array('connect' => 'true');
        }

        $connectString = sprintf('mongodb://%s:%d', $hosts, $port);
        $this->mongoManager = new Manager($connectString, $auth);
        $this->database = $database;
    }

    /**
     * Closes and destruct the MongoDB connection
     */
    public function __destruct()
    {
        // $this->mongoManager->
    }

    /**
     * Gets the instance of MongoDB; You do not need uses this directly.
     * If you have to, probably something is missing in this class
     * @return Manager|null
     */
    public function getDbConnection(): ?Manager
    {
        return $this->mongoManager;
    }

    /**
     * @param string $idDocument
     * @param null $collection
     * @return NoSqlDocument|null
     * @throws Exception
     */
    public function getDocumentById(string $idDocument, mixed $collection = null): ?NoSqlDocument
    {
        $filter = new IteratorFilter();
        $filter->addRelation('_id', Relation::EQUAL, $idDocument);
        $document = $this->getDocuments($filter, $collection);

        if (empty($document)) {
            return null;
        }

        return $document[0];
    }

    /**
     * @param IteratorFilter $filter
     * @param null $collection
     * @return NoSqlDocument[]|null
     * @throws Exception
     */
    public function getDocuments(IteratorFilter $filter, mixed $collection = null): ?array
    {
        if (empty($collection)) {
            throw new InvalidArgumentException('Collection is mandatory for MongoDB');
        }

        $dataCursor = $this->mongoManager->executeQuery(
            $this->database . '.' . $collection,
            $this->getMongoFilterArray($filter)
        );

        if (empty($dataCursor)) {
            return null;
        }

        $data = $dataCursor->toArray();

        $result = [];
        foreach ($data as $item) {
            $result[] = new NoSqlDocument(
                $item->_id,
                $collection,
                SerializerObject::instance($item)
                    ->withDoNotParse($this->excludeMongoClass)
                    ->serialize()
            );
        }

        return $result;
    }

    protected function getMongoFilterArray(IteratorFilter $filter): Query
    {
        $result = [];

        foreach ($filter->getRawFilters() as $itemFilter) {
            $name = $itemFilter[1];
            $relation = $itemFilter[2];
            $value = $itemFilter[3];

            if ($itemFilter[0] == ' or ') {
                throw new InvalidArgumentException('MongoDBDriver does not support the addRelationOr');
            }

            if (isset($result[$name])) {
                throw new InvalidArgumentException('MongoDBDriver does not support filtering the same field twice');
            }

            $data = [
                Relation::EQUAL => function ($value) {
                    return $value;
                },
                Relation::GREATER_THAN => function ($value) {
                    return [ '$gt' => $value ];
                },
                Relation::LESS_THAN => function ($value) {
                    return [ '$lt' => $value ];
                },
                Relation::GREATER_OR_EQUAL_THAN => function ($value) {
                    return [ '$gte' => $value ];
                },
                Relation::LESS_OR_EQUAL_THAN => function ($value) {
                    return [ '$lte' => $value ];
                },
                Relation::NOT_EQUAL => function ($value) {
                    return [ '$ne' => $value ];
                },
                Relation::STARTS_WITH => function ($value) {
                    return [ '$regex' => "^$value" ];
                },
                Relation::CONTAINS => function ($value) {
                    return [ '$regex' => "$value" ];
                },
            ];
            
            $result[$name] = $data[$relation]($value);
        }

        return new Query($result);
    }

    public function deleteDocumentById(string $idDocument, mixed $collection = null): mixed
    {
        $filter = new IteratorFilter();
        $filter->addRelation('_id', Relation::EQUAL, $idDocument);
        $this->deleteDocuments($filter, $collection);
        return null;
    }


    public function deleteDocuments(IteratorFilter $filter, mixed $collection = null): mixed
    {
        if (empty($collection)) {
            throw new InvalidArgumentException('Collection is mandatory for MongoDB');
        }

        $writeConcern = new WriteConcern(WriteConcern::MAJORITY, 100);
        $bulkWrite = new BulkWrite();

        $bulkWrite->delete($this->getMongoFilterArray($filter));
        $this->mongoManager->executeBulkWrite(
            $this->database . '.' . $collection,
            $bulkWrite,
            $writeConcern
        );
        return null;
    }

    /**
     * @param NoSqlDocument $document
     * @return NoSqlDocument
     */
    public function save(NoSqlDocument $document): NoSqlDocument
    {
        if (empty($document->getCollection())) {
            throw new InvalidArgumentException('Collection is mandatory for MongoDB');
        }

        $writeConcern = new WriteConcern(WriteConcern::MAJORITY, 100);
        $bulkWrite = new BulkWrite();

        $data = SerializerObject::instance($document->getDocument())
            ->withDoNotParse($this->excludeMongoClass)
            ->serialize();

        $idDocument = $document->getIdDocument();
        if (empty($idDocument)) {
            $idDocument = $data['_id'] ?? null;
        }

        $data['updated'] = new UTCDateTime((new DateTime())->getTimestamp()*1000);
        if (empty($idDocument)) {
            $data['_id'] = $idDocument = new ObjectID();
            $data['created'] = new UTCDateTime((new DateTime())->getTimestamp()*1000);
            $bulkWrite->insert($data);
        } else {
            $data['_id'] = $idDocument;
            $bulkWrite->update(['_id' => $idDocument], ["\$set" => $data]);
        }

        $this->mongoManager->executeBulkWrite(
            $this->database . "." . $document->getCollection(),
            $bulkWrite,
            $writeConcern
        );

        $document->setDocument($data);
        $document->setIdDocument($idDocument);

        return $document;
    }

    public static function schema(): array
    {
        return ["mongodb", "mongo"];
    }
}
