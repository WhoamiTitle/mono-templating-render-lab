<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\controller;

use infrastructure\presentation\http\HttpRequest;
use infrastructure\presentation\http\JsonBodyDecoder;
use infrastructure\presentation\http\exception\BadRequestHttpException;
use infrastructure\presentation\http\exception\UnauthorizedHttpException;

abstract class AbstractJsonController
{
    public function __construct(
        private readonly JsonBodyDecoder $jsonBodyDecoder = new JsonBodyDecoder()
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    protected function body(HttpRequest $request): array
    {
        return $this->jsonBodyDecoder->decode($request);
    }

    protected function requireActorId(HttpRequest $request): string
    {
        $actorId = $request->attribute('actorId');
        if (is_string($actorId) && trim($actorId) !== '') {
            return trim($actorId);
        }

        $headerActorId = $request->header('x-actor-id');
        if ($headerActorId !== null && trim($headerActorId) !== '') {
            return trim($headerActorId);
        }

        throw new UnauthorizedHttpException('auth.actor_id.required');
    }

    protected function requireRouteParam(HttpRequest $request, string $name): string
    {
        $value = $request->routeParam($name);
        if ($value === null || trim($value) === '') {
            throw new BadRequestHttpException('request.route_param.required', ['param' => $name]);
        }

        return trim($value);
    }

    protected function requireSessionId(HttpRequest $request): string
    {
        $sessionId = $request->cookie('session_id');
        if ($sessionId !== null && trim($sessionId) !== '') {
            return trim($sessionId);
        }

        throw new UnauthorizedHttpException('auth.session_id.required');
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function requireString(array $payload, string $field): string
    {
        $value = $payload[$field] ?? null;
        if (!is_string($value) || trim($value) === '') {
            throw new BadRequestHttpException('request.field.required', ['field' => $field]);
        }

        return trim($value);
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function optionalString(array $payload, string $field): ?string
    {
        $value = $payload[$field] ?? null;
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new BadRequestHttpException('request.field.invalid_string', ['field' => $field]);
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    protected function requireArray(array $payload, string $field): array
    {
        $value = $payload[$field] ?? null;
        if (!is_array($value)) {
            throw new BadRequestHttpException('request.field.invalid_array', ['field' => $field]);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function requireInt(array $payload, string $field): int
    {
        $value = $payload[$field] ?? null;
        if (!is_int($value)) {
            throw new BadRequestHttpException('request.field.invalid_int', ['field' => $field]);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function optionalInt(array $payload, string $field): ?int
    {
        $value = $payload[$field] ?? null;
        if ($value === null) {
            return null;
        }

        if (!is_int($value)) {
            throw new BadRequestHttpException('request.field.invalid_int', ['field' => $field]);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function requireFloat(array $payload, string $field): float
    {
        $value = $payload[$field] ?? null;
        if (!is_float($value) && !is_int($value)) {
            throw new BadRequestHttpException('request.field.invalid_float', ['field' => $field]);
        }

        return (float)$value;
    }

    /**
     * @param string[] $allowedFields
     * @return array<string, mixed>
     */
    protected function filters(HttpRequest $request, array $allowedFields): array
    {
        $filters = [];

        foreach ($allowedFields as $field) {
            if (!array_key_exists($field, $request->queryParams)) {
                continue;
            }

            $value = $request->queryParams[$field];
            if ($value === '') {
                continue;
            }

            if ($value === 'true') {
                $filters[$field] = true;
                continue;
            }

            if ($value === 'false') {
                $filters[$field] = false;
                continue;
            }

            if (preg_match('/^-?\d+$/', $value) === 1) {
                $filters[$field] = (int)$value;
                continue;
            }

            $filters[$field] = $value;
        }

        return $filters;
    }
}
