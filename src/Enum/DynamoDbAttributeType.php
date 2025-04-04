<?php

namespace ByJG\AnyDataset\NoSql\Enum;

enum DynamoDbAttributeType: string
{
    /**
     * Represents a number type in DynamoDB
     */
    case NUMBER = 'N';
    
    /**
     * Represents a string type in DynamoDB
     */
    case STRING = 'S';
    
    /**
     * Represents a binary type in DynamoDB
     */
    case BINARY = 'B';
    
    /**
     * Represents a boolean type in DynamoDB
     */
    case BOOLEAN = 'BOOL';
    
    /**
     * Represents a null type in DynamoDB
     */
    case NULL = 'NULL';
    
    /**
     * Represents a map type in DynamoDB (nested attributes)
     */
    case MAP = 'M';
    
    /**
     * Represents a list type in DynamoDB (ordered collection)
     */
    case LIST = 'L';
    
    /**
     * Represents a string set type in DynamoDB
     */
    case STRING_SET = 'SS';
    
    /**
     * Represents a number set type in DynamoDB
     */
    case NUMBER_SET = 'NS';
    
    /**
     * Represents a binary set type in DynamoDB
     */
    case BINARY_SET = 'BS';
} 