<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres\migration;

use PDO;
use RuntimeException;

final class SqlMigrationRunner
{
    public function __construct(
        private readonly PDO $connection,
        private readonly MigrationConfig $config
    ) {
    }

    /**
     * @return list<string>
     */
    public function migrate(): array
    {
        $this->ensureMigrationsTable();

        $applied = $this->appliedVersions();
        $executed = [];

        foreach ($this->migrationFiles() as $path) {
            $version = basename($path);
            if (isset($applied[$version])) {
                continue;
            }

            $sql = file_get_contents($path);
            if ($sql === false) {
                throw new RuntimeException('migration.file_read_failed: ' . $path);
            }

            $this->connection->beginTransaction();

            try {
                $this->connection->exec($sql);
                $this->recordAppliedMigration($version);
                $this->connection->commit();
            } catch (\Throwable $exception) {
                $this->connection->rollBack();
                throw $exception;
            }

            $executed[] = $version;
        }

        return $executed;
    }

    private function ensureMigrationsTable(): void
    {
        $this->connection->exec(
            <<<SQL
            CREATE TABLE IF NOT EXISTS schema_migrations (
                version TEXT PRIMARY KEY,
                applied_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
            SQL
        );
    }

    /**
     * @return array<string, true>
     */
    private function appliedVersions(): array
    {
        $statement = $this->connection->query('SELECT version FROM schema_migrations ORDER BY version ASC');
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $versions = [];
        foreach ($rows as $row) {
            $versions[(string)$row['version']] = true;
        }

        return $versions;
    }

    /**
     * @return list<string>
     */
    private function migrationFiles(): array
    {
        $pattern = rtrim($this->config->directory, '/') . '/*.sql';
        $paths = glob($pattern);
        if ($paths === false) {
            throw new RuntimeException('migration.glob_failed: ' . $pattern);
        }

        sort($paths, SORT_STRING);

        return array_values(array_filter(
            $paths,
            static fn (string $path): bool => is_file($path)
        ));
    }

    private function recordAppliedMigration(string $version): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO schema_migrations (version) VALUES (:version)'
        );
        $statement->execute(['version' => $version]);
    }
}
