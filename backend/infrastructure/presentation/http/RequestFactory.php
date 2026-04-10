<?php

declare(strict_types=1);

namespace infrastructure\presentation\http;

final class RequestFactory
{
    /**
     * @param array<string, mixed> $server
     * @param array<string, string> $cookies
     * @param array<string, mixed> $attributes
     */
    public function fromGlobals(
        array $server,
        array $cookies = [],
        array $attributes = []
    ): HttpRequest {
        $method = strtoupper((string)($server['REQUEST_METHOD'] ?? 'GET'));
        $uri = (string)($server['REQUEST_URI'] ?? '/');
        $path = (string)parse_url($uri, PHP_URL_PATH);
        $queryString = (string)($server['QUERY_STRING'] ?? '');

        parse_str($queryString, $queryParams);

        return new HttpRequest(
            method: $method,
            path: $path !== '' ? $path : '/',
            headers: $this->headersFromServer($server),
            queryParams: array_map(
                static fn (mixed $value): string => is_scalar($value) ? (string)$value : '',
                is_array($queryParams) ? $queryParams : []
            ),
            cookies: $cookies,
            attributes: $attributes,
            body: file_get_contents('php://input') ?: null
        );
    }

    /**
     * @param array<string, mixed> $server
     * @return array<string, string>
     */
    private function headersFromServer(array $server): array
    {
        $headers = [];

        foreach ($server as $key => $value) {
            if (!is_string($key) || !is_scalar($value)) {
                continue;
            }

            if ($key === 'CONTENT_TYPE' || $key === 'CONTENT_LENGTH') {
                $headers[strtolower(str_replace('_', '-', $key))] = (string)$value;
                continue;
            }

            if (!str_starts_with($key, 'HTTP_')) {
                continue;
            }

            $headerName = strtolower(str_replace('_', '-', substr($key, 5)));
            $headers[$headerName] = (string)$value;
        }

        return $headers;
    }
}
