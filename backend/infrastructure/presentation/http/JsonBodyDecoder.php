<?php

declare(strict_types=1);

namespace infrastructure\presentation\http;

use infrastructure\presentation\http\exception\BadRequestHttpException;
use JsonException;

final class JsonBodyDecoder
{
    /**
     * @return array<string, mixed>
     */
    public function decode(HttpRequest $request): array
    {
        $body = trim((string)$request->body);
        if ($body === '') {
            return [];
        }

        try {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new BadRequestHttpException('request.body.invalid_json', previous: $exception);
        }

        if (!is_array($decoded)) {
            throw new BadRequestHttpException('request.body.invalid_json_object');
        }

        return $decoded;
    }
}
