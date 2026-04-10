<?php

declare(strict_types=1);

namespace infrastructure\presentation\http;

final readonly class HttpRequest
{
    /**
     * @param array<string, string> $headers
     * @param array<string, string> $queryParams
     * @param array<string, string> $cookies
     * @param array<string, string> $routeParams
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public string $method,
        public string $path,
        public array $headers = [],
        public array $queryParams = [],
        public array $cookies = [],
        public array $routeParams = [],
        public array $attributes = [],
        public ?string $body = null
    ) {
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    public function cookie(string $name): ?string
    {
        return $this->cookies[$name] ?? null;
    }

    public function routeParam(string $name): ?string
    {
        return $this->routeParams[$name] ?? null;
    }

    public function attribute(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }
}
