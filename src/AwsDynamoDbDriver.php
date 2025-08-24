<?php

namespace ByJG\AnyDataset\NoSql;

use Aws\DynamoDb\DynamoDbClient;
use Aws\Result;
use ByJG\AnyDataset\Core\AnyDataset;
use ByJG\AnyDataset\Core\Exception\NotImplementedException;
use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\NoSql\Enum\DynamoDbAttributeType;
use ByJG\Serializer\Serialize;
use ByJG\Util\Uri;
use ByJG\XmlUtil\Exception\FileException;
use ByJG\XmlUtil\Exception\XmlUtilException;
use InvalidArgumentException;
use Override;

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
    #[Override]
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

    /**
     * Prepares data for sending to DynamoDB by converting values to DynamoDB attribute format
     * 
     * @param array $array The data to prepare
     * @param array $options The options containing type definitions
     * @return array The prepared data in DynamoDB format
     */
    protected function prepareToSend($array, $options) {
        $result = [];
        foreach ($array as $key => $val) {
            $attributeType = $this->getAttributeType($options, $key);
            $result[$key] = match ($attributeType) {
                DynamoDbAttributeType::BOOLEAN->value => $this->prepareBooleanType($val),
                DynamoDbAttributeType::NULL->value => $this->prepareNullType(),
                DynamoDbAttributeType::LIST->value => $this->prepareListType($val),
                DynamoDbAttributeType::MAP->value => $this->prepareMapType($val),
                DynamoDbAttributeType::STRING_SET->value => $this->prepareStringSetType($val),
                DynamoDbAttributeType::NUMBER_SET->value => $this->prepareNumberSetType($val),
                DynamoDbAttributeType::NUMBER->value => $this->prepareNumberType($val),
                default => $this->prepareDefaultType($val, $attributeType),
            };
        }
        return $result;
    }

    protected function getAttributeType(array $options, string|int $key): string
    {
        $attributeType = $options['Types'][$key] ?? DynamoDbAttributeType::STRING->value;
        if ($attributeType instanceof DynamoDbAttributeType) {
            $attributeType = $attributeType->value;
        }
        return $attributeType;
    }

    protected function prepareBooleanType(mixed $val): array
    {
        return [DynamoDbAttributeType::BOOLEAN->value => (bool)$val];
    }

    protected function prepareNullType(): array
    {
        return [DynamoDbAttributeType::NULL->value => true];
    }

    protected function prepareListType(mixed $val): array
    {
        $formattedList = [];
        foreach ($val as $item) {
            if (is_array($item) && (isset($item['S']) || isset($item['N']) || isset($item['B']) || isset($item['BOOL']))) {
                $formattedList[] = $item;
            } elseif (is_string($item)) {
                $formattedList[] = ['S' => $item];
            } elseif (is_numeric($item)) {
                $formattedList[] = ['N' => (string)$item];
            } elseif (is_bool($item)) {
                $formattedList[] = ['BOOL' => $item];
            } elseif (is_null($item)) {
                $formattedList[] = ['NULL' => true];
            } else {
                $formattedList[] = ['S' => (string)$item];
            }
        }
        return [DynamoDbAttributeType::LIST->value => $formattedList];
    }

    protected function prepareMapType(mixed $val): array
    {
        if (is_array($val)) {
            $formattedMap = [];
            foreach ($val as $mapKey => $mapValue) {
                if (is_array($mapValue) && (isset($mapValue['S']) || isset($mapValue['N']) || isset($mapValue['B']) || isset($mapValue['BOOL']))) {
                    $formattedMap[$mapKey] = $mapValue;
                } elseif (is_string($mapValue)) {
                    $formattedMap[$mapKey] = ['S' => $mapValue];
                } elseif (is_numeric($mapValue)) {
                    $formattedMap[$mapKey] = ['N' => (string)$mapValue];
                } elseif (is_bool($mapValue)) {
                    $formattedMap[$mapKey] = ['BOOL' => $mapValue];
                } elseif (is_null($mapValue)) {
                    $formattedMap[$mapKey] = ['NULL' => true];
                } else {
                    $formattedMap[$mapKey] = ['S' => (string)$mapValue];
                }
            }
            return [DynamoDbAttributeType::MAP->value => $formattedMap];
        }
        return ['S' => (string)$val];
    }

    protected function prepareStringSetType(mixed $val): array
    {
        $stringSet = [];
        foreach ($val as $item) {
            $stringSet[] = (string)$item;
        }
        return [DynamoDbAttributeType::STRING_SET->value => $stringSet];
    }

    protected function prepareNumberSetType(mixed $val): array
    {
        $numberSet = [];
        foreach ($val as $item) {
            $numberSet[] = (string)$item;
        }
        return [DynamoDbAttributeType::NUMBER_SET->value => $numberSet];
    }

    protected function prepareNumberType(mixed $val): array
    {
        return [DynamoDbAttributeType::NUMBER->value => (string)$val];
    }

    protected function prepareDefaultType(mixed $val, string $attributeType): array
    {
        return [$attributeType => (string)$val];
    }

    /**
     * Extracts a record from DynamoDB response format to regular array
     * 
     * @param mixed $awsResult The result from AWS
     * @return array|null The extracted record
     */
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
            // Handle different attribute types
            if (isset($val[DynamoDbAttributeType::NUMBER->value])) {
                // Convert string number to int or float
                $numberVal = (string)$val[DynamoDbAttributeType::NUMBER->value];
                $result[$key] = is_numeric($numberVal) ? 
                    (strpos($numberVal, '.') !== false ? (float)$numberVal : (int)$numberVal) : 
                    $numberVal;
            } 
            else if (isset($val[DynamoDbAttributeType::STRING->value])) {
                $result[$key] = $val[DynamoDbAttributeType::STRING->value];
            }
            else if (isset($val[DynamoDbAttributeType::BOOLEAN->value])) {
                $result[$key] = (bool)$val[DynamoDbAttributeType::BOOLEAN->value];
            }
            else if (isset($val[DynamoDbAttributeType::NULL->value])) {
                $result[$key] = null;
            }
            else if (isset($val[DynamoDbAttributeType::LIST->value])) {
                // Extract list items
                $list = [];
                foreach ($val[DynamoDbAttributeType::LIST->value] as $item) {
                    if (isset($item[DynamoDbAttributeType::STRING->value])) {
                        $list[] = $item[DynamoDbAttributeType::STRING->value];
                    } 
                    else if (isset($item[DynamoDbAttributeType::NUMBER->value])) {
                        $numberVal = (string)$item[DynamoDbAttributeType::NUMBER->value];
                        $list[] = is_numeric($numberVal) ? 
                            (strpos($numberVal, '.') !== false ? (float)$numberVal : (int)$numberVal) : 
                            $numberVal;
                    }
                    else if (isset($item[DynamoDbAttributeType::BOOLEAN->value])) {
                        $list[] = (bool)$item[DynamoDbAttributeType::BOOLEAN->value];
                    }
                    else if (isset($item[DynamoDbAttributeType::NULL->value])) {
                        $list[] = null;
                    }
                    else {
                        // For other complex types, store as is
                        $list[] = $item;
                    }
                }
                $result[$key] = $list;
            }
            else if (isset($val[DynamoDbAttributeType::MAP->value])) {
                // Extract map items
                $map = [];
                foreach ($val[DynamoDbAttributeType::MAP->value] as $mapKey => $mapValue) {
                    if (isset($mapValue[DynamoDbAttributeType::STRING->value])) {
                        $map[$mapKey] = $mapValue[DynamoDbAttributeType::STRING->value];
                    } 
                    else if (isset($mapValue[DynamoDbAttributeType::NUMBER->value])) {
                        $numberVal = (string)$mapValue[DynamoDbAttributeType::NUMBER->value];
                        $map[$mapKey] = is_numeric($numberVal) ? 
                            (strpos($numberVal, '.') !== false ? (float)$numberVal : (int)$numberVal) : 
                            $numberVal;
                    }
                    else if (isset($mapValue[DynamoDbAttributeType::BOOLEAN->value])) {
                        $map[$mapKey] = (bool)$mapValue[DynamoDbAttributeType::BOOLEAN->value];
                    }
                    else if (isset($mapValue[DynamoDbAttributeType::NULL->value])) {
                        $map[$mapKey] = null;
                    }
                    else {
                        // For other complex types, store as is
                        $map[$mapKey] = $mapValue;
                    }
                }
                $result[$key] = $map;
            }
            else if (isset($val[DynamoDbAttributeType::STRING_SET->value])) {
                $result[$key] = $val[DynamoDbAttributeType::STRING_SET->value];
            }
            else if (isset($val[DynamoDbAttributeType::NUMBER_SET->value])) {
                // Convert string numbers to numbers
                $numberSet = [];
                foreach ($val[DynamoDbAttributeType::NUMBER_SET->value] as $num) {
                    $strNum = (string)$num;
                    $numberSet[] = is_numeric($strNum) ? 
                        (strpos($strNum, '.') !== false ? (float)$strNum : (int)$strNum) : 
                        $strNum;
                }
                $result[$key] = $numberSet;
            }
            else {
                // Default fallback for other types
                $result[$key] = $val;
            }
        });

        return $result;
    }

    #[Override]
    public function get(string|int|object $key, array $options = []): ?array
    {
        $this->validateOptions($options);

        $keyArr = $this->prepareToSend(
            [
                $options["KeyName"] => $key
            ],
            $options
        );

        // Use the table name from options if provided, or fall back to the class property
        $tableName = $options['TableName'] ?? $this->table;
        
        if (empty($tableName)) {
            throw new InvalidArgumentException("TableName must be provided in options or in the connection string");
        }

        $data = [
            'ConsistentRead' => true,
            'TableName' => $tableName,
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
    #[Override]
    public function put(string|int|object $key, mixed $value, array $options = []): Result
    {
        if (is_object($value)) {
            $value = Serialize::from($value)->toArray();
        }

        $this->validateOptions($options);

        // Add the key to the value array if it doesn't exist
        if (is_array($value) && !isset($value[$options["KeyName"]])) {
            $value[$options["KeyName"]] = $key;
        }
        
        // Prepare the data for DynamoDB format
        $value = $this->prepareToSend($value, $options);
        
        // Use the table name from options if provided, or fall back to the class property
        $tableName = $options['TableName'] ?? $this->table;
        
        if (empty($tableName)) {
            throw new InvalidArgumentException("TableName must be provided in options or in the connection string");
        }

        $data = [
            'TableName' => $tableName,
            'Item' => $value
        ];

        return $this->dynamoDbClient->putItem($data);
    }

    /**
     * @param KeyValueDocument[] $keyValueArray
     * @param array $options
     * @return mixed
     */
    #[Override]
    public function putBatch(array $keyValueArray, array $options = []): mixed
    {
        // TODO: Implement putBatch() method.
        return null;
    }

    #[Override]
    public function remove(string|int|object $key, array $options = []): Result
    {
        $this->validateOptions($options);

        $keyArr = $this->prepareToSend(
            [
                $options["KeyName"] => $key
            ],
            $options
        );

        // Use the table name from options if provided, or fall back to the class property
        $tableName = $options['TableName'] ?? $this->table;
        
        if (empty($tableName)) {
            throw new InvalidArgumentException("TableName must be provided in options or in the connection string");
        }

        $data = [
            'ConsistentRead' => true,
            'TableName' => $tableName,
            'Key'       => $keyArr
        ];

        return $this->dynamoDbClient->deleteItem($data);
    }

    #[Override]
    public function getDbConnection(): DynamoDbClient
    {
        return $this->dynamoDbClient;
    }

    /**
     * @param object[] $keys
     * @param array $options
     * @return mixed
     */
    #[Override]
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

    #[Override]
    public static function schema(): array
    {
        return ["dynamo", "dynamodb"];
    }

    /**
     * @throws NotImplementedException
     */
    #[Override]
    public function rename(string|int|object $oldKey, string|int|object $newKey): void
    {
        throw new NotImplementedException("DynamoDB cannot rename");
    }

    #[Override]
    public function has(string|int|object $key, $options = []): bool
    {
        $value = $this->get($key, $options);
        return !empty($value);
    }

    #[Override]
    public function getChunk(object|int|string $key, array $options = [], int $size = 1024, int $offset = 0): mixed
    {
        throw new NotImplementedException("DynamoDB cannot getChunk");
    }
}
