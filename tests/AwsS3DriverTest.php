<?php

namespace TestsDb\AnyDataset;

use ByJG\AnyDataset\NoSql\AwsS3Driver;
use ByJG\AnyDataset\NoSql\Factory;
use PHPUnit\Framework\TestCase;

class AwsS3DriverTest extends TestCase
{
    /**
     * @var AwsS3Driver
     */
    protected $object;

    protected function setUp()
    {
        $awsConnection = getenv("S3_CONNECTION");
        if (!empty($awsConnection)) {
            $this->object = Factory::getKeyValueInstance($awsConnection);
            $this->object->remove("KEY");
            $this->object->remove("ANOTHER");
        }
    }

    protected function tearDown()
    {
        if (!empty($this->object)) {
            $this->object->remove("KEY");
            $this->object->remove("ANOTHER");
            $this->object = null;
        }
    }

    public function testBucketOperations()
    {
        if (empty($this->object)) {
            $this->markTestIncomplete("In order to test S3 you must define S3_CONNECTION");
        }

        // Get current bucket
        $iterator = $this->object->getIterator();
        $currentCount = $iterator->count();

        // Add an element
        $this->object->put("KEY", "value");
        $this->object->put("ANOTHER", "other value");

        // Check new elements
        $iterator = $this->object->getIterator();
        $this->assertEquals($currentCount + 2, $iterator->count());

        // Get elements
        $elem1 = $this->object->get("KEY");
        $this->assertEquals("value", $elem1);
        $elem2 = $this->object->get("ANOTHER");
        $this->assertEquals("other value", $elem2);

        // Remove elements
        $this->object->remove("KEY");
        $this->object->remove("ANOTHER");

        // Check new elements
        $iterator = $this->object->getIterator();
        $this->assertEquals($currentCount, $iterator->count());
    }

    public function testGetChunk()
    {
        if (empty($this->object)) {
            $this->markTestIncomplete("In order to test S3 you must define S3_CONNECTION");
        }

        $this->object->put(
            "KEY",
            str_repeat("0", 256) . str_repeat("1", 256) . str_repeat("2", 250)
        );

        $part1 = $this->object->getChunk("KEY", [], 256, 0);
        $part2 = $this->object->getChunk("KEY", [], 256, 1);
        $part3 = $this->object->getChunk("KEY", [], 256, 2);

        $this->assertEquals(str_repeat("0", 256), $part1);
        $this->assertEquals(str_repeat("1", 256), $part2);
        $this->assertEquals(str_repeat("2", 250), $part3);
    }
}
