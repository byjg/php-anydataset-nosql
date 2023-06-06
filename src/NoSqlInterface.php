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
    public function getDocumentById($idDocument, $collection = null);

    /**
     * @param IteratorFilter $filter
     * @param null $collection
     * @return NoSqlDocument[]|null
     */
    public function getDocuments(IteratorFilter $filter, $collection = null);

    /**
     * @param NoSqlDocument $document
     * @return NoSqlDocument
     */
    public function save(NoSqlDocument $document);

    /**
     * @param $idDocument
     * @param null $collection
     * @return mixed
     */
    public function deleteDocumentById($idDocument, $collection = null);

    /**
     * @param IteratorFilter $filter
     * @param null $collection
     * @return mixed
     */
    public function deleteDocuments(IteratorFilter $filter, $collection = null);

    /**
     * @return mixed
     */
    public function getDbConnection();
}
