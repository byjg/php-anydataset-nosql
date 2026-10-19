<?php

namespace Tests;

use Aws\Credentials\CredentialSources;
use Aws\S3\Exception\S3Exception;
use ByJG\AnyDataset\NoSql\AwsDynamoDbDriver;
use ByJG\AnyDataset\NoSql\AwsS3Driver;
use ByJG\Util\Uri;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * A URI without a user leaves the credentials to the AWS SDK default provider chain; the
 * environment is its first step, so these tests set it and expect the "env" source.
 */
class AwsCredentialsTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        putenv('AWS_ACCESS_KEY_ID=envkey');
        putenv('AWS_SECRET_ACCESS_KEY=envsecret');
    }

    #[Override]
    protected function tearDown(): void
    {
        putenv('AWS_ACCESS_KEY_ID');
        putenv('AWS_SECRET_ACCESS_KEY');
    }

    public static function dynamoDbProvider(): array
    {
        // connection, source, key, secret
        return [
            'no userinfo' => ['dynamodb://us-east-1/table', CredentialSources::ENVIRONMENT, 'envkey', 'envsecret'],
            'key only' => ['dynamodb://key@us-east-1/table', CredentialSources::STATIC, 'key', ''],
            'key and secret' => ['dynamodb://key:secret@us-east-1/table', CredentialSources::STATIC, 'key', 'secret'],
            'secret "0"' => ['dynamodb://key:0@us-east-1/table', CredentialSources::STATIC, 'key', '0'],
        ];
    }

    #[DataProvider('dynamoDbProvider')]
    public function testDynamoDbCredentials(string $connection, string $source, string $key, string $secret): void
    {
        // The client is created without a request to AWS
        $credentials = (new AwsDynamoDbDriver($connection))->client()->getCredentials()->wait();

        $this->assertSame($source, $credentials->getSource());
        $this->assertSame($key, $credentials->getAccessKeyId());
        $this->assertSame($secret, $credentials->getSecretKey());
    }

    /**
     * The S3 driver sends a HeadBucket in the constructor, so it needs the local S3 server.
     */
    protected function s3Connection(string $userInfo): string
    {
        $s3Connection = getenv('S3_CONNECTION');
        if (empty($s3Connection)) {
            $this->markTestIncomplete('In order to test S3 you must define S3_CONNECTION');
        }
        $uri = new Uri($s3Connection);

        return 's3://' . $userInfo . $uri->getHost() . $uri->getPath() . '?' . $uri->getQuery()
            . '&use_path_style_endpoint=true';
    }

    public function testS3WithoutUserUsesTheDefaultProvider(): void
    {
        // The local server accepts these from the environment, as it does from the URI
        $s3 = new Uri((string)getenv('S3_CONNECTION'));
        putenv('AWS_ACCESS_KEY_ID=' . $s3->getUsername());
        putenv('AWS_SECRET_ACCESS_KEY=' . $s3->getPassword());

        $credentials = (new AwsS3Driver($this->s3Connection('')))->client()->getCredentials()->wait();

        $this->assertSame(CredentialSources::ENVIRONMENT, $credentials->getSource());
        $this->assertSame($s3->getUsername(), $credentials->getAccessKeyId());
    }

    public function testS3WithKeyOnlySignsWithAnEmptySecret(): void
    {
        $s3 = new Uri((string)getenv('S3_CONNECTION'));

        // The SDK accepts the static key with an empty secret; the server rejects the signature
        try {
            new AwsS3Driver($this->s3Connection($s3->getUsername() . '@'));
            $this->fail('The server accepted an empty secret');
        } catch (S3Exception $ex) {
            $this->assertSame(403, $ex->getStatusCode());
        }
    }
}
