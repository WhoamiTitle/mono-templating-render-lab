<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres;

use PDO;

final class PostgresConnectionFactory
{
    /**
     * @param array{
     *   host?: string,
     *   port?: int,
     *   dbname: string,
     *   user: string,
     *   password: string,
     *   sslmode?: string
     * } $config
     */
    public static function create(array $config): PDO
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 5432;
        $sslmode = $config['sslmode'] ?? 'prefer';

        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s;sslmode=%s',
            $host,
            $port,
            $config['dbname'],
            $sslmode
        );

        return new PDO(
            $dsn,
            $config['user'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
}
