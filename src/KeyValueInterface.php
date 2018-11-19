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

    public function put($key, $value, $contentType = null, $options = []);

    public function remove($key, $options = []);

    public function getChunk($key, $options = [], $size = 1024, $offset = 0);

    public function getDbConnection();

}
