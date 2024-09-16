<?php


namespace ByJG\AnyDataset\NoSql;


class KeyValueDocument
{
    protected string $key;

    protected mixed $value;

    /**
     * KeyValueDocument constructor.
     * @param $key
     * @param $value
     */
    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }
}