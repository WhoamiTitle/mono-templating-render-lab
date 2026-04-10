<?php

declare(strict_types=1);

namespace infrastructure\presentation\http;

final class ResponseEmitter
{
    public function emit(HttpResponse $response): void
    {
        http_response_code($response->statusCode);

        foreach ($response->headers as $name => $values) {
            $normalizedName = $this->normalizeHeaderName($name);
            foreach ($values as $value) {
                header($normalizedName . ': ' . $value, false);
            }
        }

        if ($response->body !== null) {
            echo $response->body;
        }
    }

    private function normalizeHeaderName(string $name): string
    {
        $parts = explode('-', str_replace('_', '-', strtolower($name)));

        return implode('-', array_map(
            static fn (string $part): string => ucfirst($part),
            $parts
        ));
    }
}
