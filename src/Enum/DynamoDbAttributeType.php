<?php

namespace ByJG\AnyDataset\NoSql\Enum;

enum DynamoDbAttributeType: string
{
    /**
     * Represents a number type in DynamoDB
     */
    case Number = 'N';
    
    /**
     * Represents a string type in DynamoDB
     */
    case String = 'S';
    
    /**
     * Represents a binary type in DynamoDB
     */
    case Binary = 'B';
    
    /**
     * Represents a boolean type in DynamoDB
     */
    case Boolean = 'BOOL';
    
    /**
     * Represents a null type in DynamoDB
     */
    case Null = 'NULL';
    
    /**
     * Represents a map type in DynamoDB (nested attributes)
     */
    case Map = 'M';
    
    /**
     * Represents a list type in DynamoDB (ordered collection)
     */
    case List = 'L';
    
    /**
     * Represents a string set type in DynamoDB
     */
    case StringSet = 'SS';
    
    /**
     * Represents a number set type in DynamoDB
     */
    case NumberSet = 'NS';
    
    /**
     * Represents a binary set type in DynamoDB
     */
    case BinarySet = 'BS';
} 