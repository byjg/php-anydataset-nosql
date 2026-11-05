<?php

namespace ByJG\AnyDataset\NoSql;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\Row;
use Closure;
use Generator;
use Override;

/**
 * The items of a DynamoDB Query or Scan. The pages are requested while the rows are read, one at a time: only
 * the current page is in memory.
 */
class AwsDynamoDbIterator extends GenericIterator
{
    /**
     * @param iterable<array> $items The items of the paginator, from DynamoDbClient::getIterator()
     * @param Closure(array): ?array $extractRecord Converts an item from the DynamoDB attribute format
     */
    public function __construct(private iterable $items, private Closure $extractRecord)
    {
    }

    #[Override]
    protected function rows(): Generator
    {
        return self::walk($this->items, $this->extractRecord);
    }

    /**
     * @param iterable<array> $items
     * @param Closure(array): ?array $extractRecord
     */
    private static function walk(iterable $items, Closure $extractRecord): Generator
    {
        foreach ($items as $item) {
            $record = $extractRecord($item);
            if ($record !== null) {
                yield Row::factory($record);
            }
        }
    }
}
