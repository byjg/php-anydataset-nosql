---
apply: always
---

# Bofore run the unit tests:

The test WILL FAIL IF NOT FOLLOW THIS RULE.
You NEED TO EXECUTE ALL THESE STEPS BEFORE AND AFTER IMPLEMENT ANYTHING.

## Start the test infrastructure (MongoDB, MinIO for S3, DynamoDB Local)

docker compose up -d

## Set environment variables for connecting to the local services

export MONGODB_CONNECTION="mongodb://127.0.0.1/test"
export S3_CONNECTION="s3://aaa:12345678@us-east-1/mybucket?create=true&endpoint=http://127.0.0.1:4566"
export DYNAMODB_CONNECTION="dynamodb://accesskey:secretkey@us-east-1/tablename?endpoint=http://127.0.0.1:8000"

## Run the tests

vendor/bin/phpunit
