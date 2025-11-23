<?php

namespace ByJG\AnyDataset\NoSql;

use ByJG\AnyDataset\Core\AnyDataset;
use ByJG\AnyDataset\Core\Exception\NotImplementedException;
use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\Serializer\Serialize;
use ByJG\Util\Uri;
use ByJG\WebRequest\Exception\CurlException;
use ByJG\WebRequest\Exception\MessageException;
use ByJG\WebRequest\Exception\NetworkException;
use ByJG\WebRequest\Exception\RequestException;
use ByJG\WebRequest\HttpClient;
use ByJG\WebRequest\Psr7\MemoryStream;
use ByJG\WebRequest\Psr7\Request;
use Override;
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

        $this->username = $uri->getUsername() ?? '';
        $this->password = $uri->getPassword() ?? '';
        $this->accountId = $uri->getHost();
        $this->namespaceId = $uri->getPath();

        $this->kvUri = "https://api.cloudflare.com/client/v4/accounts/" .
            $this->accountId . "/storage/kv/namespaces" .
            $this->namespaceId;
    }

    /**
     * @param string|int|object $key
     * @param array $options
     * @return string
     * @throws MessageException
     * @throws NetworkException
     * @throws RequestException
     */
    #[Override]
    public function get(string|int|object $key, array $options = []): string
    {
        /** @psalm-suppress InvalidCast */
        $keyStr = is_object($key) ? (string)$key : $key;
        $request = $this->request("/values/$keyStr", $options)
            ->withMethod("get");

        return $this->send($request);
    }

    /**
     * @param string|int|object $key
     * @param mixed $value
     * @param array $options
     * @return mixed
     * @throws CurlException
     * @throws MessageException
     * @throws NetworkException
     * @throws RequestException
     */
    #[Override]
    public function put(string|int|object $key, mixed $value, array $options = []): mixed
    {
        /** @psalm-suppress InvalidCast */
        $keyStr = is_object($key) ? (string)$key : $key;
        $request = $this->request("/values/$keyStr", $options)
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
    #[Override]
    public function putBatch(array $keyValueArray, array $options = []): mixed
    {
        $request = $this->request("/bulk", $options)
            ->withMethod("put")
            ->withHeader("Content-Type", "application/json")
            ->withBody(new MemoryStream(json_encode(Serialize::from($keyValueArray)->toArray())));

        return $this->checkResult(
            $this->send($request)
        );
    }

    /**
     * @param string|int|object $key
     * @param array $options
     * @return string
     * @throws MessageException
     * @throws NetworkException
     * @throws RequestException
     */
    #[Override]
    public function remove(string|int|object $key, array $options = []): string
    {
        /** @psalm-suppress InvalidCast */
        $keyStr = is_object($key) ? (string)$key : $key;
        $request = $this->request("/values/$keyStr", $options)
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
    #[Override]
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
     * @param string $endpoint
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
    #[Override]
    public function getIterator(array $options = []): GenericIterator
    {
        $request = $this->request("/keys", $options)
            ->withMethod("get");

        $result = $this->checkResult(
            $this->send($request)
        );
        $this->lastCursor = $options;
        $this->lastCursor["cursor"] = $result["result_info"]["cursor"];

        $arrayDataset = new AnyDataset($result["result"]);

        return $arrayDataset->getIterator();
    }

    public function getLastCursor(): array
    {
        return $this->lastCursor;
    }

    #[Override]
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

    #[Override]
    public static function schema(): array
    {
        return ["kv"];
    }

    #[Override]
    public function rename(string|int|object $oldKey, string|int|object $newKey): void
    {
        throw new NotImplementedException("Not implemented");
    }

    #[Override]
    public function has(string|int|object $key, $options = []): bool
    {
        throw new NotImplementedException("Not implemented");
    }

    #[Override]
    public function getChunk(object|int|string $key, array $options = [], int $size = 1024, int $offset = 0): mixed
    {
        throw new NotImplementedException("Not implemented");
    }
}
