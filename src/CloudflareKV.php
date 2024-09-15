<?php

namespace ByJG\AnyDataset\NoSql;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Lists\ArrayDataset;
use ByJG\Serializer\SerializerObject;
use ByJG\Util\Exception\CurlException;
use ByJG\Util\Exception\MessageException;
use ByJG\Util\Exception\NetworkException;
use ByJG\Util\Exception\RequestException;
use ByJG\Util\HttpClient;
use ByJG\Util\Psr7\MemoryStream;
use ByJG\Util\Psr7\Request;
use ByJG\Util\Uri;
use Psr\Http\Message\RequestInterface;

class CloudflareKV implements KeyValueInterface, RegistrableInterface
{
    protected string $username;
    protected string $password;
    protected string $accountId;
    protected string $namespaceId;

    private string $kvUri;

    private array $lastCursor;

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
     * @param string $key
     * @param array $options
     * @return string
     * @throws MessageException
     * @throws NetworkException
     * @throws RequestException
     */
    public function get(string $key, array $options = []): string
    {
        $request = $this->request("/values/$key", $options)
            ->withMethod("get");

        return $this->send($request);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param array $options
     * @return mixed
     * @throws CurlException
     * @throws MessageException
     * @throws NetworkException
     * @throws RequestException
     */
    public function put(string $key, mixed $value, array $options = []): mixed
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
     * @return mixed
     * @throws CurlException
     * @throws MessageException
     * @throws NetworkException
     * @throws RequestException
     */
    public function putBatch(array $keyValueArray, array $options = []): mixed
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
     * @param string $key
     * @param array $options
     * @return string
     * @throws MessageException
     * @throws NetworkException
     * @throws RequestException
     */
    public function remove(string $key, array $options = []): string
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
     * @throws NetworkException
     * @throws RequestException
     */
    public function removeBatch(array $keys, array $options = []): mixed
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
     * @throws RequestException
     * @throws NetworkException
     */
    protected function send(RequestInterface $request): string
    {
        return HttpClient::getInstance()
            ->sendRequest($request)->getBody()->getContents();
    }

    /**
     * @param $endpoint
     * @param array $options
     * @return Request
     * @throws MessageException
     */
    protected function request(string $endpoint, array $options = []): Request
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
     * @return GenericIterator
     * @throws CurlException
     * @throws MessageException
     * @throws NetworkException
     * @throws RequestException
     */
    public function getIterator(array $options = []): GenericIterator
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

    public function getLastCursor(): array
    {
        return $this->lastCursor;
    }

    public function getDbConnection(): mixed
    {
        return null;
    }

    /**
     * @param $str
     * @return mixed
     * @throws CurlException
     */
    protected function checkResult($str): array
    {
        $array = json_decode($str, true);
        if (isset($array["errors"]) && !$array["success"]) {
            $errorMsg = "";
            foreach ($array["errors"] as $error) {
                $errorMsg .= "[{$error["code"]}] {$error["message"]}\n";
            }
            throw new CurlException($errorMsg);
        }
        return $array;
    }

    public static function schema(): array
    {
        return ["kv"];
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
