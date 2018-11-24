<?php

namespace ByJG\AnyDataset\NoSql;

use Aws\DynamoDb\DynamoDbClient;
use Aws\Result;
use ByJG\AnyDataset\Core\Exception\NotImplementedException;
use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Lists\ArrayDataset;
use ByJG\Serializer\BinderObject;
use ByJG\Util\Uri;

class AwsDynamoDbDriver implements KeyValueInterface
{

    /**
     * @var DynamoDbClient
     */
    protected $dynamoDbClient;

    /**
     * @var string
     */
    protected $table;

    /**
     * AwsS3Driver constructor.
     *
     *  s3://key:secret@region/bucket
     *
     * @param string $connectionString
     */
    public function __construct($connectionString)
    {
        $uri = new Uri($connectionString);

        $this->dynamoDbClient = new DynamoDbClient([
            'version'     => 'latest',
            'region'      => $uri->getHost(),
            'credentials' => [
                'key'    => $uri->getUsername(),
                'secret' => $uri->getPassword(),
            ],
        ]);

        $this->table = preg_replace('~^/~', '', $uri->getPath());
    }

    /**
     * @param array $options
     * @return GenericIterator
     */
    public function getIterator($options = [])
    {
        $data = array_merge(
            [
                'TableName' => $this->table,
            ],
            $options
        );

        if (empty($data["KeyConditions"]) && empty($data["ScanFilter"])) {
            throw new \InvalidArgumentException("You must pass KeyConditions OR ScanFilter in \$options");
        }

        if (!empty($data["KeyConditions"]) && !empty($data["ScanFilter"])) {
            throw new \InvalidArgumentException("You can pass only KeyConditions OR ScanFilter in \$options at time");
        }

        $iterator = $this->dynamoDbClient->getIterator(
            !empty($data["KeyConditions"]) ? 'Query' : 'Scan',
            $data
        );

        $result = [];
        foreach ($iterator as $item) {
            $result[] = $this->extractRecord($item);
        }

        return (new ArrayDataset($result))->getIterator();
    }

    protected function validateOptions($options) {
        if (!isset($options["KeyName"])) {
            throw new \InvalidArgumentException("KeyName is required in \$options");
        }
    }

    protected function prepareToSend($array, $options) {
        array_walk($array, function(&$val, $key) use ($options) {
            if (!is_array($val)) {
                $val = "".$val;
            }

            $val = [
                isset($options['Types']) && isset($options['Types'][$key]) ? $options['Types'][$key] : "S" => $val
            ];
        });

        return $array;
    }

    protected function extractRecord($awsResult) {
        $result = [];

        $raw = $awsResult;
        if ($awsResult instanceof Result) {
            $raw = $awsResult["Item"];
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

    public function get($key, $options = [])
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
     * @param $key
     * @param $value
     * @param array $options
     * @return Result
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function put($key, $value, $options = [])
    {
        if (is_object($value)) {
            $value = BinderObject::toArrayFrom($value);
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

    public function remove($key, $options = [])
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

    public function getDbConnection()
    {
        return $this->dynamoDbClient;
    }

    /**
     * @param $key
     * @param array $options
     * @param int $size
     * @param int $offset
     * @throws NotImplementedException
     */
    public function getChunk($key, $options = [], $size = 1024, $offset = 0)
    {
        throw new NotImplementedException("Get Chunk is not implemented for DynamoDb");
    }
}
