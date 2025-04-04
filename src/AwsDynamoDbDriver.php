<?php

namespace ByJG\AnyDataset\NoSql;

use Aws\DynamoDb\DynamoDbClient;
use Aws\Result;
use ByJG\AnyDataset\Core\AnyDataset;
use ByJG\AnyDataset\Core\Exception\NotImplementedException;
use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\Serializer\Serialize;
use ByJG\Util\Uri;
use ByJG\XmlUtil\Exception\FileException;
use ByJG\XmlUtil\Exception\XmlUtilException;
use InvalidArgumentException;

class AwsDynamoDbDriver implements KeyValueInterface, RegistrableInterface
{

    /**
     * @var DynamoDbClient
     */
    protected DynamoDbClient $dynamoDbClient;

    /**
     * @var string|array|null
     */
    protected string|array|null $table;

    /**
     * AwsS3Driver constructor.
     *
     *  s3://key:secret@region/bucket
     *
     * @param string $connectionString
     */
    public function __construct(string $connectionString)
    {
        $uri = new Uri($connectionString);

        $defaultParameters = [
            'version'     => 'latest',
            'region'      => $uri->getHost(),
            'credentials' => [
                'key'    => $uri->getUsername(),
                'secret' => $uri->getPassword(),
            ],
        ];

        $extraParameters = [];
        parse_str($uri->getQuery(), $extraParameters);

        $dynamoDbParameters = array_merge($defaultParameters, $extraParameters);

        $this->dynamoDbClient = new DynamoDbClient($dynamoDbParameters);

        $this->table = preg_replace('~^/~', '', $uri->getPath());
    }

    /**
     * @param array $options
     * @return GenericIterator
     * @throws FileException
     * @throws XmlUtilException
     */
    public function getIterator(array $options = []): GenericIterator
    {
        $data = array_merge(
            [
                'TableName' => $this->table,
            ],
            $options
        );

        if (empty($data["KeyConditions"]) && empty($data["ScanFilter"])) {
            throw new InvalidArgumentException("You must pass KeyConditions OR ScanFilter in \$options");
        }

        if (!empty($data["KeyConditions"]) && !empty($data["ScanFilter"])) {
            throw new InvalidArgumentException("You can pass only KeyConditions OR ScanFilter in \$options at time");
        }

        $iterator = $this->dynamoDbClient->getIterator(
            !empty($data["KeyConditions"]) ? 'Query' : 'Scan',
            $data
        );

        $result = [];
        foreach ($iterator as $item) {
            $result[] = $this->extractRecord($item);
        }

        return (new AnyDataset($result))->getIterator();
    }

    protected function validateOptions($options): void
    {
        if (!isset($options["KeyName"])) {
            throw new InvalidArgumentException("KeyName is required in \$options");
        }
    }

    protected function prepareToSend($array, $options) {
        array_walk($array, function(&$val, $key) use ($options) {
            if (!is_array($val)) {
                $val = "".$val;
            }

            $val = [
                $options['Types'][$key] ?? "S" => $val
            ];
        });

        return $array;
    }

    protected function extractRecord($awsResult): ?array
    {
        $result = [];

        $raw = $awsResult;
        if ($awsResult instanceof Result) {
            $raw = $awsResult["Item"];
        }

        if (empty($raw)) {
            return null;
        }

        array_walk($raw, function($val, $key) use (&$result) {
            $value = null;
            if (isset($val["N"])) {
                $value = intval($val["N"]);
            } else if (isset($val["S"])) {
                $value = $val["S"];
            }

            $result[$key] = $value;
        });

        return $result;
    }

    public function get(string|int|object $key, array $options = []): ?array
    {
        $this->validateOptions($options);

        $keyArr = $this->prepareToSend(
            [
                $options["KeyName"] => $key
            ],
            $options
        );

        $data = [
            'ConsistentRead' => true,
            'TableName' => $this->table,
            'Key'       => $keyArr
        ];

        $result = $this->dynamoDbClient->getItem($data);

        return $this->extractRecord($result);
    }

    /**
     * @param string|int|object $key
     * @param mixed $value
     * @param array $options
     * @return Result
     */
    public function put(string|int|object $key, mixed $value, array $options = []): Result
    {
        if (is_object($value)) {
            $value = Serialize::from($value)->toArray();
        }

        $this->validateOptions($options);

        $value[$options["KeyName"]] = $key;
        $value = $this->prepareToSend($value, $options);

        $data = [
            'TableName' => $this->table,
            'Item' => $value
        ];

        return $this->dynamoDbClient->putItem($data);
    }

    /**
     * @param KeyValueDocument[] $keyValueArray
     * @param array $options
     * @return mixed
     */
    public function putBatch(array $keyValueArray, array $options = []): mixed
    {
        // TODO: Implement putBatch() method.
        return null;
    }

    public function remove(string|int|object $key, array $options = []): Result
    {
        $this->validateOptions($options);

        $keyArr = $this->prepareToSend(
            [
                $options["KeyName"] => $key
            ],
            $options
        );

        $data = [
            'ConsistentRead' => true,
            'TableName' => $this->table,
            'Key'       => $keyArr
        ];

        return $this->dynamoDbClient->deleteItem($data);
    }

    public function getDbConnection(): DynamoDbClient
    {
        return $this->dynamoDbClient;
    }

    /**
     * @param object[] $keys
     * @param array $options
     * @return mixed
     */
    public function removeBatch(array $keys, array $options = []): mixed
    {
        // TODO: Implement removeBatch() method.
        return null;
    }

    public function getTablename(): array|string|null
    {
        return $this->table;
    }

    public function client(): DynamoDbClient
    {
        return $this->dynamoDbClient;
    }

    public static function schema(): array
    {
        return ["dynamo", "dynamodb"];
    }

    /**
     * @throws NotImplementedException
     */
    public function rename(string|int|object $oldKey, string|int|object $newKey): void
    {
        throw new NotImplementedException("DynamoDB cannot rename");
    }

    public function has(string|int|object $key, $options = []): bool
    {
        $value = $this->get($key, $options);
        return !empty($value);
    }

    public function getChunk(object|int|string $key, array $options = [], int $size = 1024, int $offset = 0): mixed
    {
        throw new NotImplementedException("DynamoDB cannot getChunk");
    }
}
