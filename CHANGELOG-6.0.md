# Changelog - Version 6.0

## Overview

Version 6.0 represents a major update with significant improvements to type safety, code quality, and modern PHP compatibility. This release includes breaking changes that require migration steps from version 5.x.

## New Features

### Type-Safe DynamoDB Attribute Definitions

- **DynamoDb Enum Support**: Introduced `DynamoDbAttributeType` enum for type-safe attribute definitions
  - All DynamoDB attribute types now available as enum cases: `STRING`, `NUMBER`, `BINARY`, `BOOLEAN`, `NULL`, `MAP`, `LIST`, `STRING_SET`, `NUMBER_SET`, `BINARY_SET`
  - Improved type safety when working with DynamoDB attributes
  - Better IDE autocomplete and compile-time validation

### Enhanced DynamoDB Driver

- **Advanced Attribute Handling**: Completely refactored DynamoDB attribute preparation logic
  - Dedicated methods for each attribute type: `prepareBooleanType()`, `prepareNullType()`, `prepareListType()`, `prepareMapType()`, `prepareStringSetType()`, `prepareNumberSetType()`, `prepareNumberType()`
  - Improved readability and maintainability
  - Better handling of complex nested data structures (Maps and Lists)
  - Proper type conversion for numbers (int/float detection)

- **Flexible Table Name Support**: `TableName` can now be provided in options or connection string for more flexible configuration

### Code Quality Improvements

- **Psalm Static Analysis**:
  - Dedicated Psalm workflow with SARIF analysis upload to GitHub
  - Psalm-specific suppress annotations for necessary type casting
  - Improved type safety across the codebase
  - Better null-safety handling with standardized null-coalescing logic

- **Override Attributes**: Added `#[Override]` attributes to all interface implementations for better IDE support and compile-time validation

### CI/CD Enhancements

- **GitHub Actions Updates**:
  - Added privileged container options for better test infrastructure support
  - Updated `actions/checkout` to version 5
  - Enhanced environment variable support for DynamoDB, MongoDB, and S3 connections in `phpunit.xml.dist`
  - Separated Psalm workflow for dedicated static analysis

### Documentation

- **Updated Test Documentation**: Enhanced testing documentation with new `docker compose` syntax (replacing `docker-compose`)
- **GitHub Sponsors**: Added GitHub Sponsors badge to README
- **Improved Logger Handling**: Better logger handling in `KeyValueCacheEngine` with proper null defaults

## Breaking Changes

| Component | Before (5.x) | After (6.0) | Description |
|-----------|--------------|-------------|-------------|
| **PHP Version** | `>=8.1 <8.4` | `>=8.3 <8.6` | Minimum PHP version raised to 8.3, added support for 8.4 and 8.5 |
| **Dependencies** | `byjg/anydataset: ^5.0`<br/>`byjg/anydataset-array: ^5.0`<br/>`byjg/cache-engine: ^5.0`<br/>`byjg/serializer: ^5.0`<br/>`byjg/webrequest: ^5.0` | `byjg/anydataset: ^6.0`<br/>`byjg/cache-engine: ^6.0`<br/>`byjg/serializer: ^6.0`<br/>`byjg/webrequest: ^6.0` | All ByJG dependencies upgraded to version 6.0. **Note**: `byjg/anydataset-array` removed as dependency |
| **PHPUnit** | `^9.6` | `^10.5\|^11.5` | PHPUnit upgraded to versions 10.5 or 11.5 |
| **Psalm** | `^5.9` | `^5.9\|^6.13` | Psalm now supports version 6.13 |
| **ArrayDataset** | `use ByJG\AnyDataset\Lists\ArrayDataset;` | `use ByJG\AnyDataset\Core\AnyDataset;` | `ArrayDataset` replaced with `AnyDataset` throughout the codebase |
| **DynamoDB Attribute Types** | String-based type definitions (`"S"`, `"N"`, etc.) | `DynamoDbAttributeType` enum | While strings still work, enum usage is recommended for type safety |
| **Number Handling** | Numbers stored as strings or generic values | Proper int/float type conversion | DynamoDB numbers now properly converted to int or float based on value |
| **MongoDB Driver** | `writeConcern` parameter formatting | Adjusted `writeConcern` parameter formatting | Minor changes to parameter handling |
| **Null Handling** | Inconsistent null-coalescing logic | Standardized null-coalescing across all drivers | More predictable null handling behavior |

## Bug Fixes

- **Type Safety**: Fixed various type casting issues identified by Psalm static analysis
- **MongoDB**: Adjusted `writeConcern` parameter formatting in `MongoDbDriver`
- **Test Infrastructure**: Enhanced `MongoDbDriverTest` to handle missing test infrastructure gracefully
- **S3 Driver**: Improved null-safety with proper null-coalescing operators
- **Cloudflare KV**: Better type handling and null-safety improvements
- **Cache Engine**: Improved logger handling with proper default values

## Migration Guide: 5.x to 6.0

### Step 1: Update PHP Version

Ensure your environment is running PHP 8.3 or higher:

```bash
php -v  # Should show PHP 8.3.x, 8.4.x, or 8.5.x
```

### Step 2: Update Dependencies

Update your `composer.json`:

```bash
composer require "byjg/anydataset-nosql:^6.0"
composer update
```

This will automatically update all related ByJG dependencies to version 6.0.

### Step 3: Replace ArrayDataset References

If your code directly uses `ArrayDataset`, replace it with `AnyDataset`:

```php
// Before (5.x)
use ByJG\AnyDataset\Lists\ArrayDataset;
$dataset = new ArrayDataset($data);

// After (6.0)
use ByJG\AnyDataset\Core\AnyDataset;
$dataset = new AnyDataset($data);
```

### Step 4: Update DynamoDB Attribute Types (Recommended)

While string-based type definitions still work, it's recommended to use the new enum for type safety:

```php
// Before (5.x)
use ByJG\AnyDataset\NoSql\AwsDynamoDbDriver;

$options = [
    'KeyName' => 'id',
    'Types' => [
        'id' => 'S',
        'count' => 'N',
        'active' => 'BOOL',
    ]
];

// After (6.0) - Recommended
use ByJG\AnyDataset\NoSql\AwsDynamoDbDriver;
use ByJG\AnyDataset\NoSql\Enum\DynamoDbAttributeType;

$options = [
    'KeyName' => 'id',
    'Types' => [
        'id' => DynamoDbAttributeType::STRING,
        'count' => DynamoDbAttributeType::NUMBER,
        'active' => DynamoDbAttributeType::BOOLEAN,
    ]
];
```

### Step 5: Update Test Suite (If Applicable)

If you have custom tests, update PHPUnit configuration:

```bash
composer require --dev "phpunit/phpunit:^10.5"
# or
composer require --dev "phpunit/phpunit:^11.5"
```

Update Psalm if used:

```bash
composer require --dev "vimeo/psalm:^6.13"
```

### Step 6: Update Docker Compose Commands

If you use Docker Compose in your scripts or documentation, update the syntax:

```bash
# Before (5.x)
docker-compose up -d

# After (6.0)
docker compose up -d
```

### Step 7: Review Number Type Handling

DynamoDB numbers are now properly typed as `int` or `float`. Review any code that assumes numbers are always strings:

```php
// Before (5.x) - numbers might be strings
$count = (int)$result['count'];

// After (6.0) - numbers are properly typed
$count = $result['count']; // Already int or float
```

### Step 8: Test Your Application

Run your test suite to ensure everything works correctly:

```bash
composer test
composer psalm  # If using Psalm
```

## Additional Notes

- **Backwards Compatibility**: While most changes are internal, the removal of `byjg/anydataset-array` dependency and the switch to `AnyDataset` may require code updates
- **Performance**: No significant performance changes expected
- **Security**: Improved type safety reduces the risk of type-related bugs
- **Documentation**: All documentation has been updated to reflect version 6.0 changes

## Credits

This release includes contributions focused on modernizing the codebase, improving type safety, and enhancing developer experience through better tooling and documentation.
