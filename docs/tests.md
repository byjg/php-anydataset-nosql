---
sidebar_position: 6
---

# Running Unit tests

```bash
docker-compose up -d
export MONGODB_CONNECTION="mongodb://127.0.0.1/test"
export S3_CONNECTION="s3://aaa:12345678@us-east-1/mybucket?create=true&endpoint=http://127.0.0.1:4566"
export DYNAMODB_CONNECTION="dynamodb://accesskey:secretkey@us-east-1/tablename?endpoint=http://127.0.0.1:8000"
vendor/bin/phpunit
```

## Setup the environment variables

| Variable                | Description                         | Example                                         |
|-------------------------|-------------------------------------|-------------------------------------------------|
| MONGODB_CONNECTION      | Connection string for MongoDB       | mongodb://127.0.0.1/test                        |
| S3_CONNECTION           | Connection string for S3            | s3://accesskey:secretkey@region/bucketname      |
| DYNAMODB_CONNECTION     | Connection string for DynamoDB      | dynamodb://accesskey:secretkey@region/tablename |
| CLOUDFLAREKV_CONNECTION | Connection string for Cloudflare KV | kv://email:authkey@accountid/namespaceid        |

