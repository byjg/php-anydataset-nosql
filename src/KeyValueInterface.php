<?php

namespace ByJG\AnyDataset\NoSql;

use ByJG\AnyDataset\Core\GenericIterator;

interface KeyValueInterface
{
    /**
     * @param array $options
     * @return GenericIterator
     */
    public function getIterator(array $options = []): GenericIterator;

    public function has(string $key, array $options = []);

    public function get(string $key, array $options = []): mixed;

    public function put(string $key, mixed $value, array $options = []): mixed;

    /**
     * @param KeyValueDocument[] $keyValueArray
     * @param array $options
     * @return void
     */
    public function putBatch(array $keyValueArray, array $options = []): mixed;

    public function remove(string $key, array $options = []): mixed;

    /**
     * @param object[] $keys
     * @param array $options
     * @return void
     */
    public function removeBatch(array $keys, array $options = []): mixed;

    public function getDbConnection(): mixed;

    public function rename($oldKey, $newKey);

}
