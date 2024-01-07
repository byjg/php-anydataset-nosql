<?php

namespace ByJG\AnyDataset\NoSql;

use ByJG\AnyDataset\Core\IteratorFilter;

interface NoSqlInterface
{

    /**
     * Return a NoSqlDocument or null if not found
     *
     * @param mixed $idDocument
     * @param mixed $collection
     * @return NoSqlDocument|null
     */
    public function getDocumentById(string $idDocument, mixed $collection = null): ?NoSqlDocument;

    /**
     * @param IteratorFilter $filter
     * @param null $collection
     * @return NoSqlDocument[]|null
     */
    public function getDocuments(IteratorFilter $filter, $collection = null): array|null;

    /**
     * @param NoSqlDocument $document
     * @return NoSqlDocument
     */
    public function save(NoSqlDocument $document): mixed;

    /**
     * @param $idDocument
     * @param null $collection
     * @return mixed
     */
    public function deleteDocumentById(string $idDocument, mixed $collection = null): mixed;

    /**
     * @param IteratorFilter $filter
     * @param null $collection
     * @return mixed
     */
    public function deleteDocuments(IteratorFilter $filter, mixed $collection = null): mixed;

    /**
     * @return mixed
     */
    public function getDbConnection(): mixed;
}
