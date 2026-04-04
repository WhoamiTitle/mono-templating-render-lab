<?php

declare(strict_types=1);

namespace infrastructure\bootstrap;

final readonly class PostgresConfig
{
    public function __construct(
        public string $dbname,
        public string $user,
        public string $password,
        public string $host = '127.0.0.1',
        public int $port = 5432,
        public string $sslmode = 'prefer',
        public string $sessionTtlSpec = 'P7D'
    ) {
    }

    /**
     * @param array<string, string> $env
     */
    public static function fromEnv(array $env): self
    {
        return new self(
            dbname: $env['POSTGRES_DB'],
            user: $env['POSTGRES_USER'],
            password: $env['POSTGRES_PASSWORD'],
            host: $env['POSTGRES_HOST'] ?? '127.0.0.1',
            port: isset($env['POSTGRES_PORT']) ? (int)$env['POSTGRES_PORT'] : 5432,
            sslmode: $env['POSTGRES_SSLMODE'] ?? 'prefer',
            sessionTtlSpec: $env['SESSION_TTL_SPEC'] ?? 'P7D'
        );
    }
}
