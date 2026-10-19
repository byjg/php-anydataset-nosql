<?php

namespace Tests;

use ByJG\AnyDataset\NoSql\Factory;
use PHPUnit\Framework\TestCase;

/**
 * The Manager connects lazily, so these tests do not need a MongoDB server.
 */
class MongoDbDriverOptionsTest extends TestCase
{
    public function testUriOptionsReachTheManager()
    {
        $manager = Factory::getInstance(
            'mongodb://127.0.0.1/test?uri.w=majority&uri.wtimeoutMS=500&uri.readPreference=secondaryPreferred'
        )->getDbConnection();

        $this->assertSame('majority', $manager->getWriteConcern()->getW());
        $this->assertSame(500, $manager->getWriteConcern()->getWtimeout());
        $this->assertSame('secondaryPreferred', $manager->getReadPreference()->getModeString());
    }

    public function testDriverOptionsReachTheManagerWithoutPrefix()
    {
        // The Manager only validates the type of the driver options it knows, so an invalid
        // value proves "driver.autoEncryption" arrived as "autoEncryption".
        $this->expectException(\MongoDB\Driver\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected "autoEncryption" driver option to be array');

        Factory::getInstance('mongodb://127.0.0.1/test?driver.autoEncryption=x');
    }

    public function testReplicaSetWithSingleSeedHostReachesTheManager()
    {
        $manager = Factory::getInstance('mongodb://127.0.0.1/test?uri.replicaSet=rs0')->getDbConnection();

        // The Manager has no getter for the connection string; it is only exposed in its debug info.
        $this->assertStringContainsString('mongodb://127.0.0.1:27017/?replicaSet=rs0', print_r($manager, true));
    }

    public function testCredentialsArePercentEncoded()
    {
        // The input URI is percent-encoded; the connection string must keep it encoded once.
        $manager = Factory::getInstance('mongodb://us%40er:p%40ss%3A%2F%25w@127.0.0.1/test')->getDbConnection();

        $this->assertStringContainsString('mongodb://us%40er:p%40ss%3A%2F%25w@127.0.0.1:27017/?', print_r($manager, true));
    }

    public function testNoCredentialsProduceNoUserInfo()
    {
        $manager = Factory::getInstance('mongodb://127.0.0.1/test')->getDbConnection();

        $this->assertStringContainsString('mongodb://127.0.0.1:27017/?', print_r($manager, true));
    }

    public function testMultipleHostsAreRejected()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Multiple hosts are not supported');

        Factory::getInstance('mongodb://user:pass@server1,server2,server3/test?uri.replicaSet=rs0');
    }

    public function testOptionWithoutPrefixIsRejected()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid option 'replicaSet'");

        Factory::getInstance('mongodb://127.0.0.1/test?replicaSet=rs0');
    }
}
