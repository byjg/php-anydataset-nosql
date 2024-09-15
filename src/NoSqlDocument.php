<?php

namespace ByJG\AnyDataset\NoSql;

use ByJG\Serializer\BinderObject;

class NoSqlDocument
{
    protected ?string $idDocument;

    protected ?string $collection;

    protected mixed $document;

    protected array $subDocument = [];


    /**
     * NoSqlDocument constructor.
     *
     * @param string|null $idDocument
     * @param string|null $collection
     * @param array $document
     */
    public function __construct(?string $idDocument = null, ?string $collection = null, array $document = [])
    {
        $this->idDocument = $idDocument;
        $this->collection = $collection;

        $this->setDocument($document);
    }

    /**
     * @return string|null
     */
    public function getIdDocument(): ?string
    {
        return $this->idDocument;
    }

    /**
     * @param string|null $idDocument
     * @return $this
     */
    public function setIdDocument(?string $idDocument): self
    {
        $this->idDocument = $idDocument;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCollection(): ?string
    {
        return $this->collection;
    }

    /**
     * @param null $collection
     * @return $this
     */
    public function setCollection(mixed $collection): self
    {
        $this->collection = $collection;
        return $this;
    }


    /**
     * @return array|object
     */
    public function getDocument($entityClass = null): array
    {
        if (is_null($entityClass)) {
            return $this->document;
        }

        $entity = new $entityClass();
        BinderObject::bind($this->document, $entity);

        return $entity;
    }

    /**
     * @param array $document
     * @return $this
     */
    public function setDocument(array $document): self
    {
        $this->document = $document;
        return $this;
    }

    public function addSubDocument(NoSqlDocument $document): void
    {
        $this->subDocument[] = $document;
    }
}
