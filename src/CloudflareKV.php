<?php

namespace ByJG\AnyDataset\NoSql;

use ByJG\AnyDataset\Core\IteratorInterface;
use ByJG\AnyDataset\Lists\ArrayDataset;
use ByJG\Serializer\BinderObject;
use ByJG\Serializer\Exception\InvalidArgumentException;
use ByJG\Util\CurlException;
use ByJG\Util\Uri;
use ByJG\Util\WebRequest;

class CloudflareKV implements KeyValueInterface
{
    protected $username;
    protected $password;
    protected $accountId;
    protected $namespaceId;

    private $kvUri;

    private $lastCursor;

    public function __construct($connectionString)
    {
        $uri = new Uri($connectionString);

        $this->username = $uri->getUsername();
        $this->password = $uri->getPassword();
        $this->accountId = $uri->getHost();
        $this->namespaceId = $uri->getPath();

        $this->kvUri = "https://api.cloudflare.com/client/v4/accounts/" .
            $this->accountId . "/storage/kv/namespaces" .
            $this->namespaceId;
    }

    /**
     * @param $key
     * @param array $options
     * @return string
     * @throws CurlException
     */
    public function get($key, $options = [])
    {
        return $this->webRequest("/values/$key", $options)->get();
    }

    /**
     * @param $key
     * @param $value
     * @param array $options
     * @return string
     * @throws CurlException
     */
    public function put($key, $value, $options = [])
    {
        return $this->checkResult(
            $this->webRequest("/values/$key", $options)->putPayload($value)
        );
    }

    /**
     * @param KeyValueDocument[] $keyValueArray
     * @param array $options
     * @return string
     * @throws CurlException
     * @throws InvalidArgumentException
     */
    public function putBatch($keyValueArray, $options = [])
    {
        return $this->checkResult(
            $this->webRequest("/bulk", $options)->putPayload(
                json_encode(BinderObject::toArrayFrom($keyValueArray)),
                "application/json"
            )
        );
    }

    /**
     * @param $key
     * @param array $options
     * @return string
     * @throws CurlException
     */
    public function remove($key, $options = [])
    {
        return $this->webRequest("/values/$key", $options)->delete();
    }

    /**
     * @param object[] $keys
     * @param array $options
     * @return mixed
     * @throws CurlException
     */
    public function removeBatch($keys, $options = [])
    {
        return $this->checkResult(
            $this->webRequest("/bulk", $options)->deletePayload(
                json_encode($keys),
                "application/json"
            )
        );
    }

    /**
     * @param $method
     * @param array $options
     * @return WebRequest
     */
    protected function webRequest($method, $options = [])
    {
        $uri = new Uri($this->kvUri . $method);
        $uri->withQuery(http_build_query($options));
        $webRequest = new WebRequest($uri->__toString());
        $webRequest->addRequestHeader("X-Auth-Email", $this->username);
        $webRequest->addRequestHeader("X-Auth-Key", $this->password);

        return $webRequest;
    }

    /**
     * Options:
     *   prefix: ""
     *
     * @param array $options
     * @return IteratorInterface
     * @throws CurlException
     */
    public function getIterator($options = [])
    {
        $result = $this->checkResult(
            $this->webRequest("/keys", $options)->get()
        );
        $this->lastCursor = $options;
        $this->lastCursor["cursor"] = $result["result_info"]["cursor"];

        $arrayDataset = new ArrayDataset($result["result"]);

        return $arrayDataset->getIterator();
    }

    public function getLastCursor()
    {
        return $this->lastCursor;
    }

    public function getDbConnection()
    {
        return null;
    }

    /**
     * @param $str
     * @return mixed
     * @throws CurlException
     */
    protected function checkResult($str)
    {
        $array = json_decode($str, true);
        if (isset($array["success"]) && !$array["success"]) {
            $errorMsg = "";
            foreach ($array["errors"] as $error) {
                $errorMsg .= "[${error["code"]}] ${error["message"]}\n";
            }
            throw new CurlException($errorMsg);
        }
        return $array;
    }
}
