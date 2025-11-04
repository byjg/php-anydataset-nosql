---
sidebar_position: 6
title: Running Tests
description: How to run the test suite for AnyDataset NoSQL
---

# Running Tests

This library includes comprehensive tests for all supported drivers. You can run the tests using Docker for local
testing or configure connections to actual cloud services.

## Using Docker for Local Testing

A Docker Compose configuration is provided to set up local testing environments:

```bash
# Start the test infrastructure (MongoDB, MinIO for S3, DynamoDB Local)
docker compose up -d

# Set environment variables for connecting to the local services
export MONGODB_CONNECTION="mongodb://127.0.0.1/test"
export S3_CONNECTION="s3://aaa:12345678@us-east-1/mybucket?create=true&endpoint=http://127.0.0.1:4566"
export DYNAMODB_CONNECTION="dynamodb://accesskey:secretkey@us-east-1/tablename?endpoint=http://127.0.0.1:8000"

# Run the tests
vendor/bin/phpunit
```

## Environment Variables

The following environment variables can be configured to customize the test connections:

| Variable                | Description                         | Example                                                |
|-------------------------|-------------------------------------|--------------------------------------------------------|
| MONGODB_CONNECTION      | Connection string for MongoDB       | mongodb://127.0.0.1/test                               |
| S3_CONNECTION           | Connection string for S3            | s3://accesskey:secretkey@region/bucketname?create=true |
| DYNAMODB_CONNECTION     | Connection string for DynamoDB      | dynamodb://accesskey:secretkey@region/tablename        |
| CLOUDFLAREKV_CONNECTION | Connection string for Cloudflare KV | kv://email:authkey@accountid/namespaceid               |

## Running Specific Test Suites

To run tests for a specific driver only:

```bash
# Run only MongoDB tests
vendor/bin/phpunit --group mongodb

# Run only S3 tests
vendor/bin/phpunit --group s3

# Run only DynamoDB tests
vendor/bin/phpunit --group dynamodb 

# Run only CloudFlare KV tests
vendor/bin/phpunit --group cloudflare
```

## Skipping Tests

:::tip Selective Testing
If you don't have access to a specific service, you can skip the corresponding tests by not setting the environment
variable. Tests for services without configured environment variables will be skipped automatically.
:::

```bash
# Only run MongoDB and S3 tests
export MONGODB_CONNECTION="mongodb://127.0.0.1/test"
export S3_CONNECTION="s3://accesskey:secretkey@us-east-1/bucketname?create=true"
vendor/bin/phpunit
```
