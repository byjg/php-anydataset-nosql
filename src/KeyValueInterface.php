<?php

namespace ByJG\AnyDataset\NoSql;

use ByJG\AnyDataset\Core\GenericIterator;

interface KeyValueInterface
{

    /**
     * @param array $options
     * @return GenericIterator
     */
    public function getIterator($options = []);

    public function get($key, $options = []);

    public function put($key, $value, $options = []);

    /**
     * @param KeyValueDocument[] $keyValueArray
     * @param array $options
     * @return void
     */
    public function putBatch($keyValueArray, $options = []);

    public function remove($key, $options = []);

    /**
     * @param object[] $key
     * @param array $options
     * @return mixed
     */
    public function removeBatch($key, $options = []);

    public function getDbConnection();

}
