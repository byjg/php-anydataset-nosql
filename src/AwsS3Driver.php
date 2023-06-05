<?php

namespace ByJG\AnyDataset\NoSql;

use Aws\Result;
use Aws\S3\S3Client;
use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Lists\ArrayDataset;
use ByJG\Util\Uri;

class AwsS3Driver implements KeyValueInterface, RegistrableInterface
{

    /**
     * @var S3Client
     */
    protected $s3Client;

    /**
     * @var string
     */
    protected $bucket;

    /**
     * AwsS3Driver constructor.
     *
     *  s3://key:secret@region/bucket
     *
     * @param string $connectionString
     */
    public function __construct($connectionString)
    {
        $uri = new Uri($connectionString);

        $defaultParameters =
            [
                'version'     => 'latest',
                'region'      => $uri->getHost(),
                'credentials' => [
                    'key'    => $uri->getUsername(),
                    'secret' => $uri->getPassword(),
                ],
            ];

        $extraParameters = [];
        parse_str($uri->getQuery(), $extraParameters);

        $createBucket = false;
        if (isset($extraParameters["create"])) {
            $createBucket = ($extraParameters["create"] == "true");
            unset($extraParameters["create"]);
        }

        $s3Parameters = array_merge($defaultParameters, $extraParameters);

        $this->s3Client = new S3Client($s3Parameters);

        $this->bucket = preg_replace('~^/~', '', $uri->getPath());

        try {
            $result = $this->s3Client->headBucket([
                'Bucket' => $this->bucket,
            ]);
        } catch (\Aws\S3\Exception\S3Exception $ex) {
            if (strpos($ex->getMessage(), "404") !== false && $createBucket) {
                $this->s3Client->createBucket([
                    'ACL' => 'private',
                    'Bucket' => $this->bucket,
                    'CreateBucketConfiguration' => [
                        'LocationConstraint' => $uri->getHost(),
                    ],
                ]);
            } else {
                throw $ex;
            }
        }
    }

    /**
     * @param array $options
     * @return GenericIterator
     */
    public function getIterator($options = [])
    {
        $data = array_merge(
            [
                'Bucket' => $this->bucket,
            ],
            $options
        );

        /**
         * @var Result
         */
        $result = $this->s3Client->listObjects($data);

        $contents = [];
        if (isset($result['Contents'])) {
            $contents = $result['Contents'];
        }
        return (new ArrayDataset($contents))->getIterator();
    }

    public function get($key, $options = [])
    {
        $data = array_merge(
            [
                'Bucket' => $this->bucket,
                'Key'    => $key
            ],
            $options
        );

        $result = $this->s3Client->getObject($data);

        return $result["Body"]->getContents();
    }

    public function put($key, $value, $options = [])
    {
        $data = array_merge(
            [
                'Bucket' => $this->bucket,
                'Key'    => $key,
                'Body'   => $value,
            ],
            $options
        );

//        if (!empty($contentType)) {
//            $data['ContentType'] = $contentType;
//        }

        if (!isset($data['ACL'])) {
            $data['ACL'] = 'private';
        }

        return $this->s3Client->putObject($data);
    }

    public function remove($key, $options = [])
    {
        $data = array_merge(
            [
                'Bucket' => $this->bucket,
                'Key'    => $key
            ],
            $options
        );

        $this->s3Client->deleteObject($data);
    }

    public function getDbConnection()
    {
        return $this->s3Client;
    }

    public function getChunk($key, $options = [], $size = 1024, $offset = 0)
    {
        $part = ($offset * $size);

        $untilByte = ($part + $size - 1);

        $options = array_merge(
            $options,
            [
                'Range' => "bytes=${part}-${untilByte}"
            ]
        );

        return $this->get($key, $options);
    }

    /**
     * @param KeyValueDocument[] $keyValueArray
     * @param array $options
     * @return void
     */
    public function putBatch($keyValueArray, $options = [])
    {
        // TODO: Implement putBatch() method.
    }

    /**
     * @param object[] $keys
     * @param array $options
     * @return mixed
     */
    public function removeBatch($keys, $options = [])
    {
        // TODO: Implement removeBatch() method.
    }

    public function client() {
        return $this->s3Client;
    }

    public static function schema()
    {
        return "s3";
    }
}
