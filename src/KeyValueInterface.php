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

    public function has(string|int|object $key, array $options = []): bool;

    public function get(string|int|object $key, array $options = []): mixed;

    public function put(string|int|object $key, mixed $value, array $options = []): mixed;

    public function getChunk(string|int|object $key, array $options = [], int $size = 1024, int $offset = 0): mixed;

    /**
     * @param KeyValueDocument[] $keyValueArray
     * @param array $options
     * @return mixed
     */
    public function putBatch(array $keyValueArray, array $options = []): mixed;

    public function remove(string|int|object $key, array $options = []): mixed;

    /**
     * @param object[] $keys
     * @param array $options
     * @return mixed
     */
    public function removeBatch(array $keys, array $options = []): mixed;

    public function getDbConnection(): mixed;

    public function rename(string|int|object $oldKey, string|int|object $newKey): void;

}
