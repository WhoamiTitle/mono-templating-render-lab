<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres;

use InvalidArgumentException;

final class PostgresFilterBuilder
{
    /**
     * @param array<string, mixed> $filters
     * @param array<string, string> $allowedColumns
     * @return array{sql: string, params: array<string, mixed>}
     */
    public static function build(array $filters, array $allowedColumns): array
    {
        $parts = [];
        $params = [];

        foreach ($filters as $property => $value) {
            if (!array_key_exists($property, $allowedColumns)) {
                throw new InvalidArgumentException('repository.postgres.unsupported_filter: ' . $property);
            }

            $paramName = 'filter_' . $property;
            $parts[] = sprintf('%s = :%s', $allowedColumns[$property], $paramName);
            $params[$paramName] = $value;
        }

        if ($parts === []) {
            return [
                'sql' => '',
                'params' => [],
            ];
        }

        return [
            'sql' => ' AND ' . implode(' AND ', $parts),
            'params' => $params,
        ];
    }
}
