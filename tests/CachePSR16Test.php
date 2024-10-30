<?php


use ByJG\AnyDataset\NoSql\Cache\KeyValueCacheEngine;
use ByJG\AnyDataset\NoSql\Factory;
use ByJG\Cache\Psr16\BaseCacheEngine;
use ByJG\Cache\Psr16\NoCacheEngine;
use ByJG\Util\Uri;
use PHPUnit\Framework\TestCase;

class CachePSR16Test extends TestCase
{
    protected ?BaseCacheEngine $cacheEngine = null;

    public function CachePoolProvider()
    {
        $result = [];
        $awsConnection = getenv("S3_CONNECTION");
        if (!empty($awsConnection)) {
            $uri = new Uri($awsConnection);
            $uri = $uri->withQueryKeyValue("use_path_style_endpoint", "true");
            $object = Factory::getInstance($uri);
            $object->remove("KEY");
            $object->remove("ANOTHER");
            $result[] = [new KeyValueCacheEngine($object)];
        }

        return $result;
    }

    protected function tearDown(): void
    {
        if (empty($this->cacheEngine)) {
            return;
        }
        $this->cacheEngine->deleteMultiple(['chave', 'chave2', 'chave3']);
        $this->cacheEngine = null;
    }


    /**
     * @dataProvider CachePoolProvider
     * @param BaseCacheEngine $cacheEngine
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function testGetOneItem(BaseCacheEngine $cacheEngine)
    {
        $this->cacheEngine = $cacheEngine;

        if ($cacheEngine->isAvailable()) {
            // First time
            $item = $cacheEngine->get('chave', null);
            $this->assertNull($item);
            $item = $cacheEngine->get('chave', 'default');
            $this->assertEquals('default', $item);

            // Set object
            $cacheEngine->set('chave', 'valor');

            // Get Object
            if (!($cacheEngine instanceof NoCacheEngine)) {
                $item2 = $cacheEngine->get('chave', 'default');
                $this->assertEquals('valor', $item2);
            }

            // Remove
            $cacheEngine->delete('chave');

            // Check Removed
            $item = $cacheEngine->get('chave');
            $this->assertNull($item);
        } else {
            $this->markTestIncomplete('Object is not fully functional');
        }
    }

    /**
     * @dataProvider CachePoolProvider
     * @param BaseCacheEngine $cacheEngine
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function testGetMultipleItems(BaseCacheEngine $cacheEngine)
    {
        $this->cacheEngine = $cacheEngine;

        if ($cacheEngine->isAvailable()) {
            // First time
            $items = [...$cacheEngine->getMultiple(['chave1', 'chave2'])];
            $this->assertNull($items['chave1']);
            $this->assertNull($items['chave2']);
            $items = [...$cacheEngine->getMultiple(['chave1', 'chave2'], 'default')];
            $this->assertEquals('default', $items['chave1']);
            $this->assertEquals('default', $items['chave2']);

            // Set object
            $cacheEngine->set('chave1', 'valor1');
            $cacheEngine->set('chave2', 'valor2');

            // Get Object
            if (!($cacheEngine instanceof NoCacheEngine)) {
                $item2 = [...$cacheEngine->getMultiple(['chave1', 'chave2'])];
                $this->assertEquals('valor1', $item2['chave1']);
                $this->assertEquals('valor2', $item2['chave2']);
            }

            // Remove
            $cacheEngine->deleteMultiple(['chave1', 'chave2']);

            // Check Removed
            $items = [...$cacheEngine->getMultiple(['chave1', 'chave2'])];
            $this->assertNull($items['chave1']);
            $this->assertNull($items['chave2']);
        } else {
            $this->markTestIncomplete('Object is not fully functional');
        }
    }

    /**
     * @dataProvider CachePoolProvider
     * @param BaseCacheEngine $cacheEngine
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function testTtl(BaseCacheEngine $cacheEngine)
    {
        $this->cacheEngine = $cacheEngine;

        if ($cacheEngine->isAvailable()) {
            // First time
            $item = $cacheEngine->get('chave');
            $this->assertNull($item);
            $this->assertFalse($cacheEngine->has('chave'));
            $item2 = $cacheEngine->get('chave2');
            $this->assertNull($item2);
            $this->assertFalse($cacheEngine->has('chave2'));

            // Set object
            $cacheEngine->set('chave', 'valor', 2);
            $cacheEngine->set('chave2', 'valor2', 2);

            // Get Object
            if (!($cacheEngine instanceof NoCacheEngine)) {
                $item2 = $cacheEngine->get('chave');
                $this->assertEquals('valor', $item2);
                $this->assertTrue($cacheEngine->has('chave2'));
                sleep(3);
                $item2 = $cacheEngine->get('chave');
                $this->assertNull($item2);
                $this->assertFalse($cacheEngine->has('chave2'));
            }
        } else {
            $this->markTestIncomplete('Object is not fully functional');
        }
    }

    /**
     * @dataProvider CachePoolProvider
     * @param BaseCacheEngine $cacheEngine
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function testCacheObject(BaseCacheEngine $cacheEngine)
    {
        $this->cacheEngine = $cacheEngine;

        if ($cacheEngine->isAvailable()) {
            // First time
            $item = $cacheEngine->get('chave');
            $this->assertNull($item);

            // Set object
            $model = new \Tests\Document("name", "brand", 30);
            $cacheEngine->set('chave', $model);

            $item2 = $cacheEngine->get('chave');
            $this->assertEquals($model, $item2);

            // Delete
            $cacheEngine->delete('chave');
            $item = $cacheEngine->get('chave');
            $this->assertNull($item);
        } else {
            $this->markTestIncomplete('Object is not fully functional');
        }
    }
}
