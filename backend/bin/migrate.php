#!/usr/bin/env php
<?php

declare(strict_types=1);

use infrastructure\repository\postgres\PostgresConnectionFactory;
use infrastructure\repository\postgres\migration\MigrationConfig;
use infrastructure\repository\postgres\migration\SqlMigrationRunner;

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoloadPath)) {
    fwrite(STDERR, "vendor/autoload.php not found. Run composer install first.\n");
    exit(1);
}

require $autoloadPath;

$dbname = getenv('POSTGRES_DB');
$user = getenv('POSTGRES_USER');
$password = getenv('POSTGRES_PASSWORD');

if ($dbname === false || $user === false || $password === false) {
    fwrite(
        STDERR,
        "POSTGRES_DB, POSTGRES_USER and POSTGRES_PASSWORD env vars are required.\n"
    );
    exit(1);
}

$host = getenv('POSTGRES_HOST') ?: '127.0.0.1';
$port = getenv('POSTGRES_PORT');
$sslmode = getenv('POSTGRES_SSLMODE') ?: 'prefer';

try {
    $connection = PostgresConnectionFactory::create([
        'host' => $host,
        'port' => $port !== false && $port !== '' ? (int)$port : 5432,
        'dbname' => $dbname,
        'user' => $user,
        'password' => $password,
        'sslmode' => $sslmode,
    ]);

    $runner = new SqlMigrationRunner(
        $connection,
        new MigrationConfig(__DIR__ . '/../infrastructure/repository/postgres/sql')
    );

    $executed = $runner->migrate();
} catch (Throwable $exception) {
    fwrite(STDERR, "Migration failed: {$exception->getMessage()}\n");
    exit(1);
}

if ($executed === []) {
    fwrite(STDOUT, "No pending migrations.\n");
    exit(0);
}

foreach ($executed as $version) {
    fwrite(STDOUT, "Applied {$version}\n");
}

exit(0);
