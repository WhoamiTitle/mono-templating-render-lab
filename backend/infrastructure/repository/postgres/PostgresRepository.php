<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres;

use DateTimeImmutable;
use PDO;
use PDOStatement;

abstract class PostgresRepository
{
    public function __construct(
        protected readonly PDO $connection
    ) {
    }

    /**
     * @param array<string, mixed> $params
     */
    protected function execute(string $sql, array $params = []): void
    {
        $statement = $this->prepareAndExecute($sql, $params);
        $statement->closeCursor();
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>|null
     */
    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $statement = $this->prepareAndExecute($sql, $params);
        $row = $statement->fetch();
        $statement->closeCursor();

        return is_array($row) ? $row : null;
    }

    /**
     * @param array<string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    protected function fetchAll(string $sql, array $params = []): array
    {
        $statement = $this->prepareAndExecute($sql, $params);
        $rows = $statement->fetchAll();
        $statement->closeCursor();

        return array_map(
            static fn (mixed $row): array => is_array($row) ? $row : [],
            $rows
        );
    }

    /**
     * @param array<string, mixed> $params
     */
    private function prepareAndExecute(string $sql, array $params): PDOStatement
    {
        $statement = $this->connection->prepare($sql);

        foreach ($params as $name => $value) {
            $statement->bindValue(':' . $name, $this->normalizeParameterValue($value));
        }

        $statement->execute();

        return $statement;
    }

    protected function toTimestamp(DateTimeImmutable $value): string
    {
        return $value->format('Y-m-d H:i:s.uP');
    }

    /**
     * @param mixed $value
     */
    private function normalizeParameterValue(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_array($value)) {
            return JsonValue::encode($value);
        }

        if ($value instanceof DateTimeImmutable) {
            return $this->toTimestamp($value);
        }

        return $value;
    }
}
