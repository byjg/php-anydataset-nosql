<?php

namespace ByJG\AnyDataset\NoSql;

use ByJG\AnyDataset\Core\Exception\NotAvailableException;
use ByJG\Util\Uri;

class Factory
{
    /**
     * @param $connectionString
     * @param $schemesAlternative
     * @return NoSqlInterface
     * @throws NotAvailableException
     */
    public static function getNoSqlInstance($connectionString, $schemesAlternative = null)
    {
        $prefix = '\\ByJG\\AnyDataset\\NoSql\\';

        return self::getInstance(
            $connectionString,
            array_merge(
                [
                    "mongodb" => $prefix . "MongoDbDriver",
                ],
                (array)$schemesAlternative
            ),
            NoSqlInterface::class
        );
    }

    /**
     * @param string $connectionString
     * @param array $schemesAlternative
     * @return KeyValueInterface
     * @throws NotAvailableException
     */
    public static function getKeyValueInstance($connectionString, $schemesAlternative = null)
    {
        $prefix = '\\ByJG\\AnyDataset\\NoSql\\';

        return self::getInstance(
            $connectionString,
            array_merge(
                [
                    "s3" => $prefix . "AwsS3Driver",
                ],
                (array)$schemesAlternative
            ),
            KeyValueInterface::class
        );
    }

    /**
     * @param $connectionString
     * @param $validSchemes
     * @param $typeOf
     * @return mixed
     * @throws NotAvailableException
     */
    protected static function getInstance($connectionString, $validSchemes, $typeOf)
    {
        $connectionUri = new Uri($connectionString);

        $scheme = $connectionUri->getScheme();

        if (!isset($validSchemes[$scheme])) {
            throw new NotAvailableException("Not available: " . $scheme);
        }

        $class = $validSchemes[$scheme];

        $instance = new $class($connectionUri);

        if (!is_a($instance, $typeOf)) {
            throw new \InvalidArgumentException(
                "The class '$typeOf' is not a instance of DbDriverInterface"
            );
        }

        return $instance;
    }
}
