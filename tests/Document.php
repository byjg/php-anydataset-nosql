<?php

namespace Tests;

use MongoDB\BSON\UTCDateTime;

class Document
{
    protected ?string $_id;
    protected ?string $name;

    protected ?string $brand;

    protected ?int $price;

    protected ?UTCDateTime $createdAt;

    protected ?UTCDateTime $updatedAt;

    public function __construct(?string $name = null, ?string $brand = null, ?int $price = null)
    {
        $this->name = $name;
        $this->brand = $brand;
        $this->price = $price;
    }

    /**
     * @return string|null
     */
    public function get_id(): ?string
    {
        return $this->_id;
    }

    /**
     * @param string|null $_id
     */
    public function set_id(?string $_id): void
    {
        $this->_id = $_id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getBrand(): ?string
    {
        return $this->brand;
    }

    /**
     * @param string|null $brand
     */
    public function setBrand(?string $brand): void
    {
        $this->brand = $brand;
    }

    /**
     * @return int|null
     */
    public function getPrice(): ?int
    {
        return $this->price;
    }

    /**
     * @param int|null $price
     */
    public function setPrice(?int $price): void
    {
        $this->price = $price;
    }

    /**
     * @return mixed|null
     */
    public function getCreatedAt(): UTCDateTime
    {
        return $this->createdAt;
    }

    /**
     * @param mixed|null $createdAt
     */
    public function setCreatedAt(?UTCDateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed|null
     */
    public function getUpdatedAt(): ?UTCDateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed|null $updatedAt
     */
    public function setUpdatedAt(?UTCDateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

}