<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\route;

use infrastructure\presentation\http\attribute\Route;
use ReflectionClass;

final class AttributeRouteScanner
{
    /**
     * @param class-string[] $controllerClasses
     * @return array<int, array{method: string, path: string, controller: class-string}>
     */
    public function definitions(array $controllerClasses): array
    {
        $definitions = [];

        foreach ($controllerClasses as $controllerClass) {
            $route = $this->route($controllerClass);
            if ($route === null) {
                continue;
            }

            $definitions[] = [
                'method' => strtoupper($route->method),
                'path' => $route->path,
                'controller' => $controllerClass,
            ];
        }

        return $definitions;
    }

    /**
     * @param class-string $controllerClass
     */
    public function route(string $controllerClass): ?Route
    {
        $reflection = new ReflectionClass($controllerClass);
        $attributes = $reflection->getAttributes(Route::class);
        if ($attributes === []) {
            return null;
        }

        return $attributes[0]->newInstance();
    }
}
