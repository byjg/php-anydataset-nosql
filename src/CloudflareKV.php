<?php

namespace ByJG\AnyDataset\NoSql;

use ByJG\AnyDataset\Core\IteratorInterface;
use ByJG\AnyDataset\Lists\ArrayDataset;
use ByJG\Serializer\SerializerObject;
use ByJG\Util\Exception\CurlException;
use ByJG\Util\Exception\MessageException;
use ByJG\Util\HttpClient;
use ByJG\Util\Psr7\MemoryStream;
use ByJG\Util\Psr7\Message;
use ByJG\Util\Psr7\Request;
use ByJG\Util\Uri;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;

class CloudflareKV implements KeyValueInterface, RegistrableInterface
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
     * @throws MessageException
     */
    public function get($key, $options = [])
    {
        $request = $this->request("/values/$key", $options)
            ->withMethod("get");

        return $this->send($request);
    }

    /**
     * @param $key
     * @param $value
     * @param array $options
     * @return mixed
     * @throws CurlException
     * @throws MessageException
     */
    public function put($key, $value, $options = [])
    {
        $request = $this->request("/values/$key", $options)
            ->withMethod("put")
            ->withBody(new MemoryStream($value));

        return $this->checkResult(
            $this->send($request)
        );
    }

    /**
     * @param KeyValueDocument[] $keyValueArray
     * @param array $options
     * @return mixed|void
     * @throws CurlException
     * @throws MessageException
     */
    public function putBatch($keyValueArray, $options = [])
    {
        $request = $this->request("/bulk", $options)
            ->withMethod("put")
            ->withHeader("Content-Type", "application/json")
            ->withBody(new MemoryStream(json_encode(SerializerObject::instance($keyValueArray)->serialize())));

        return $this->checkResult(
            $this->send($request)
        );
    }

    /**
     * @param $key
     * @param array $options
     * @return string
     * @throws CurlException
     * @throws MessageException
     */
    public function remove($key, $options = [])
    {
        $request = $this->request("/values/$key", $options)
            ->withMethod("delete");

        return $this->send($request);
    }

    /**
     * @param object[] $keys
     * @param array $options
     * @return mixed
     * @throws CurlException
     * @throws MessageException
     */
    public function removeBatch($keys, $options = [])
    {
        $request = $this->request("/bulk", $options)
            ->withMethod("delete")
            ->withHeader("Content-Type", "application/json")
            ->withBody(new MemoryStream(json_encode($keys)));

        return $this->checkResult(
            $this->send($request)
        );
    }

    /**
     * @param RequestInterface $request
     * @return string
     * @throws CurlException
     * @throws MessageException
     */
    protected function send(RequestInterface $request)
    {
        return HttpClient::getInstance()
            ->sendRequest($request)->getBody()->getContents();
    }

    /**
     * @param $endpoint
     * @param array $options
     * @return Message|Request|MessageInterface
     * @throws MessageException
     */
    protected function request($endpoint, $options = [])
    {
        $uri = Uri::getInstanceFromString($this->kvUri . $endpoint)
            ->withQuery(http_build_query($options));

        return Request::getInstance($uri)
            ->withHeader("X-Auth-Email", $this->username)
            ->withHeader("X-Auth-Key", $this->password);
    }

    /**
     * Options:
     *   prefix: ""
     *
     * @param array $options
     * @return IteratorInterface
     * @throws CurlException
     * @throws MessageException
     */
    public function getIterator($options = [])
    {
        $request = $this->request("/keys", $options)
            ->withMethod("get");

        $result = $this->checkResult(
            $this->send($request)
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
                $errorMsg .= "[{$error["code"]}] {$error["message"]}\n";
            }
            throw new CurlException($errorMsg);
        }
        return $array;
    }

    public static function schema()
    {
        return "kv";
    }

    public function rename($oldKey, $newKey)
    {
        // TODO: Implement rename() method.
    }

    public function has($key, $options = [])
    {
        // TODO: Implement has() method.
    }
}
