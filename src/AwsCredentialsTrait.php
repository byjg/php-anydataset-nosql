<?php

namespace ByJG\AnyDataset\NoSql;

use ByJG\Util\Uri;

trait AwsCredentialsTrait
{
    /**
     * Static credentials from the userinfo (`key:secret@`). Without a user, no 'credentials'
     * entry is returned, so the AWS SDK uses its default provider chain (environment,
     * ~/.aws files, IAM/ECS role).
     */
    protected static function credentialsFromUri(Uri $uri): array
    {
        $key = $uri->getUsername();
        if ($key === null || $key === '') {
            return [];
        }

        return [
            'credentials' => [
                'key'    => $key,
                'secret' => $uri->getPassword() ?? '',
            ],
        ];
    }
}
