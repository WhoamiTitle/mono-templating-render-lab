<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\route;

use infrastructure\presentation\http\exception\MethodNotAllowedHttpException;
use infrastructure\presentation\http\exception\NotFoundHttpException;

final class Router
{
    /**
     * @param array<int, array{method: string, path: string, controller: object}> $routes
     */
    public function __construct(
        private readonly array $routes
    ) {
    }

    public function match(string $method, string $path): RouteMatch
    {
        $allowedMethods = [];

        foreach ($this->routes as $route) {
            $routeParams = $this->matchPath($route['path'], $path);
            if ($routeParams === null) {
                continue;
            }

            $allowedMethods[] = $route['method'];
            if (strtoupper($route['method']) !== strtoupper($method)) {
                continue;
            }

            return new RouteMatch(
                controller: $route['controller'],
                routeParams: $routeParams
            );
        }

        if ($allowedMethods !== []) {
            throw new MethodNotAllowedHttpException(
                'route.method_not_allowed',
                ['allowedMethods' => array_values(array_unique($allowedMethods))]
            );
        }

        throw new NotFoundHttpException('route.not_found', ['path' => $path]);
    }

    /**
     * @return array<string, string>|null
     */
    private function matchPath(string $routePath, string $requestPath): ?array
    {
        $pattern = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            static fn (array $matches): string => '(?P<' . $matches[1] . '>[^/]+)',
            $routePath
        );

        if ($pattern === null) {
            return null;
        }

        if (!preg_match('#^' . $pattern . '$#', $requestPath, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $params[$key] = rawurldecode($value);
            }
        }

        return $params;
    }
}
