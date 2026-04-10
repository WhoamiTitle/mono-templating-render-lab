<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres;

use RuntimeException;

final class JsonValue
{
    /**
     * @param array<mixed> $value
     */
    public static function encode(array $value): string
    {
        $encoded = json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($encoded)) {
            throw new RuntimeException('repository.postgres.json_encode_failed');
        }

        return $encoded;
    }

    /**
     * @return array<mixed>
     */
    public static function decode(string $value): array
    {
        $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new RuntimeException('repository.postgres.json_decode_failed');
        }

        return $decoded;
    }
}
