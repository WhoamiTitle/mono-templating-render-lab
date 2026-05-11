<?php

declare(strict_types=1);

use infrastructure\bootstrap\PostgresConfig;
use infrastructure\bootstrap\PostgresServiceContainer;
use infrastructure\presentation\http\HttpKernel;
use infrastructure\presentation\http\RequestFactory;
use infrastructure\presentation\http\ResponseEmitter;
use infrastructure\presentation\http\SessionAuthenticator;
use infrastructure\presentation\http\route\Router;
use infrastructure\support\SystemClock;

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoloadPath)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "vendor/autoload.php not found. Run composer install first.\n";
    exit(1);
}

require $autoloadPath;

// CORS — allow configured origin(s) with credentials
$corsOrigins = array_filter(explode(',', getenv('CORS_ORIGINS') ?: 'http://localhost:5173'));
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin && in_array($origin, $corsOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Vary: Origin');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$requiredEnvKeys = [
    'POSTGRES_DB',
    'POSTGRES_USER',
    'POSTGRES_PASSWORD',
];

$env = [];
foreach ($requiredEnvKeys as $key) {
    $value = getenv($key);
    if ($value === false) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo $key . " env var is required.\n";
        exit(1);
    }

    $env[$key] = $value;
}

foreach (['POSTGRES_HOST', 'POSTGRES_PORT', 'POSTGRES_SSLMODE', 'SESSION_TTL_SPEC'] as $optionalKey) {
    $value = getenv($optionalKey);
    if ($value !== false) {
        $env[$optionalKey] = $value;
    }
}

$container = new PostgresServiceContainer(PostgresConfig::fromEnv($env));
$kernel = new HttpKernel(
    new Router($container->commandRoutes()),
    new SessionAuthenticator($container->authSessionRepository(), new SystemClock())
);
$request = (new RequestFactory())->fromGlobals($_SERVER, $_COOKIE);

(new ResponseEmitter())->emit($kernel->handle($request));
