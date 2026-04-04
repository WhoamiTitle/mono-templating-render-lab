<?php

declare(strict_types=1);

namespace infrastructure\presentation\http;

use JsonException;

final class JsonResponse
{
    /**
     * @param array<string, mixed> $payload
     * @param array<string, string[]> $headers
     */
    public static function ok(array $payload, array $headers = []): HttpResponse
    {
        return self::create(200, $payload, $headers);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string[]> $headers
     */
    public static function created(array $payload, array $headers = []): HttpResponse
    {
        return self::create(201, $payload, $headers);
    }

    /**
     * @param array<string, string[]> $headers
     */
    public static function noContent(array $headers = []): HttpResponse
    {
        return new HttpResponse(
            statusCode: 204,
            headers: $headers
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string[]> $headers
     */
    public static function createError(int $statusCode, array $payload, array $headers = []): HttpResponse
    {
        return self::create($statusCode, $payload, $headers);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string[]> $headers
     */
    private static function create(int $statusCode, array $payload, array $headers): HttpResponse
    {
        try {
            $body = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (JsonException $exception) {
            throw new \RuntimeException('presentation.http.response_encoding_failed', 0, $exception);
        }

        $headers['content-type'] = ['application/json; charset=utf-8'];

        return new HttpResponse(
            statusCode: $statusCode,
            headers: $headers,
            body: $body
        );
    }
}
