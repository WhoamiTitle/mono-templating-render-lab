<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\route;

final readonly class RouteMatch
{
    /**
     * @param array<string, string> $routeParams
     */
    public function __construct(
        public object $controller,
        public array $routeParams,
        public bool $requiresAuth = true
    ) {
    }
}
