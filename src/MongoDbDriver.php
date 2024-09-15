<?php

namespace ByJG\AnyDataset\NoSql;

use ByJG\AnyDataset\Core\Enum\Relation;
use ByJG\AnyDataset\Core\IteratorFilter;
use ByJG\Serializer\Serialize;
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

    protected $idField;

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
        parse_str($this->connectionUri->getQuery(), $options);
        $uriOptions = [];
        $driverOptions = [];
        foreach ($options as $key => $value) {
            if (strpos($key, 'uri.') === 0) {
                $options[$key] = $value;
            } elseif (strpos($key, 'driver.') === 0) {
                $driverOptions[$key] = $value;
            } else {
                throw new InvalidArgumentException("Invalid option '$key'. Need start with 'uri.' or 'driver.'. ");
            }
        }

        if (!empty($username) && !empty($password)) {
            $auth = "$username:$password@";
        } else {
            $auth = "";
        }

        $connectString = sprintf('mongodb://%s%s:%d', $auth, $hosts, $port);
        $this->mongoManager = new Manager($connectString, $uriOptions, $driverOptions);
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
        $filter->addRelation('_id', Relation::EQUAL, new ObjectID($idDocument));
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
            new Query($this->getMongoFilterArray($filter))
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
                Serialize::from($item)
                    ->withDoNotParse($this->excludeMongoClass)
                    ->toArray()
            );
        }

        return $result;
    }

    protected function getMongoFilterArray(IteratorFilter $filter): array
    {
        $result = [];

        $data = [
            Relation::EQUAL->name => function ($value) {
                return $value;
            },
            Relation::GREATER_THAN->name => function ($value) {
                return [ '$gt' => $value ];
            },
            Relation::LESS_THAN->name => function ($value) {
                return [ '$lt' => $value ];
            },
            Relation::GREATER_OR_EQUAL_THAN->name => function ($value) {
                return [ '$gte' => $value ];
            },
            Relation::LESS_OR_EQUAL_THAN->name => function ($value) {
                return [ '$lte' => $value ];
            },
            Relation::NOT_EQUAL->name => function ($value) {
                return [ '$ne' => $value ];
            },
            Relation::STARTS_WITH->name => function ($value) {
                return [ '$regex' => "^$value" ];
            },
            Relation::CONTAINS->name => function ($value) {
                return [ '$regex' => "$value" ];
            },
            Relation::IN->name => function ($value) {
                return [ '$in' => $value ];
            },
            Relation::NOT_IN->name => function ($value) {
                return [ '$nin' => $value ];
            },
        ];


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

            $result[$name] = $data[$relation->name]($value);
        }

        return $result;
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

        $query = $this->getMongoFilterArray($filter);
        $bulkWrite = new BulkWrite();
        $bulkWrite->delete($query);
        $this->mongoManager->executeBulkWrite(
            $this->database . '.' . $collection,
            $bulkWrite,
            $writeConcern
        );

        return null;
    }

    public function updateDocuments(IteratorFilter $filter, $data, $collection = null)
    {
        if (empty($collection)) {
            throw new InvalidArgumentException('Collection is mandatory for MongoDB');
        }

        $query = $this->getMongoFilterArray($filter);
        $writeConcern = new WriteConcern(WriteConcern::MAJORITY, 100);
        $bulkWrite = new BulkWrite();
        $bulkWrite->update($query, ["\$set" => $data], ["multi" => 1]);
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

        $data = Serialize::from($document->getDocument())
            ->withDoNotParse($this->excludeMongoClass)
            ->toArray();

        $idDocument = $document->getIdDocument();
        if (empty($idDocument)) {
            $idDocument = $data['_id'] ?? null;
        }

        $data['updatedAt'] = new UTCDateTime((new DateTime())->getTimestamp()*1000);
        if (empty($idDocument)) {
            $data['_id'] = $idDocument = new ObjectID();
            $data['createdAt'] = new UTCDateTime((new DateTime())->getTimestamp()*1000);
            $bulkWrite->insert($data);
        } else {
            if (!($idDocument instanceof ObjectID)) {
                $idDocument = new ObjectID($idDocument);
            }
            $data['_id'] = $idDocument;
            $bulkWrite->update(['_id' => $idDocument], ['$set' => $data], ['multi' => false, 'upsert' => true]);
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
