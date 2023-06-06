<?php

namespace ByJG\AnyDataset\NoSql;

use ByJG\Util\Uri;
use InvalidArgumentException;

class Factory
{
    private static $config = [];

    /**
     * @param string $class
     * @return void
     */
    public static function registerDriver($class)
    {
        if (!in_array(RegistrableInterface::class, class_implements($class))) {
            throw new InvalidArgumentException(
                "The class '$class' is not a valid instance"
            );
        }

        if (empty($class::schema())) {
            throw new InvalidArgumentException(
                "The class '$class' must implement the static method schema()"
            );
        }

        $protocolList = $class::schema();
        foreach ((array)$protocolList as $item) {
            self::$config[$item] = $class;
        }
    }

    /**
     * @param $connectionUri Uri|string
     * @return NoSqlInterface|KeyValueInterface
     */
    public static function getInstance($connectionUri)
    {
        if (empty(self::$config)) {
            self::registerDriver(AwsDynamoDbDriver::class);
            self::registerDriver(AwsS3Driver::class);
            self::registerDriver(CloudflareKV::class);
            self::registerDriver(MongoDbDriver::class);
        }

        if (is_string($connectionUri)) {
            $connectionUri = new Uri($connectionUri);
        }

        $scheme = $connectionUri->getScheme();

        if (!isset(self::$config[$scheme])) {
            throw new InvalidArgumentException("The '$scheme' scheme does not exist.");
        }

        $class = self::$config[$scheme];

        return new $class($connectionUri);
    }
}
