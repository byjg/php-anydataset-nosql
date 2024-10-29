<?php

namespace ByJG\AnyDataset\NoSql;

use ByJG\AnyDataset\Core\IteratorFilter;

interface NoSqlInterface
{

    /**
     * Return a NoSqlDocument or null if not found
     *
     * @param string|object $idDocument
     * @param mixed $collection
     * @return NoSqlDocument|null
     */
    public function getDocumentById(string|object $idDocument, mixed $collection = null): ?NoSqlDocument;

    /**
     * @param IteratorFilter $filter
     * @param mixed|null $collection
     * @return array|null
     */
    public function getDocuments(IteratorFilter $filter, mixed $collection = null): array|null;

    /**
     * @param NoSqlDocument $document
     * @return NoSqlDocument
     */
    public function save(NoSqlDocument $document): mixed;

    /**
     * @param string $idDocument
     * @param null $collection
     * @return mixed
     */
    public function deleteDocumentById(string $idDocument, mixed $collection = null): mixed;

    /**
     * @param IteratorFilter $filter
     * @param mixed|null $collection
     * @return void
     */
    public function deleteDocuments(IteratorFilter $filter, mixed $collection = null): void;

    /**
     * @param IteratorFilter $filter
     * @param array $data
     * @param mixed|null $collection
     * @return void
     */
    public function updateDocuments(IteratorFilter $filter, array $data, mixed $collection = null): void;

    /**
     * @return mixed
     */
    public function getDbConnection(): mixed;
}
