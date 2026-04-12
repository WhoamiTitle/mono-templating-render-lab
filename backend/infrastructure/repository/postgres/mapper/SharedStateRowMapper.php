<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres\mapper;

use DateTimeImmutable;
use domain\state\model\SharedState;
use infrastructure\repository\postgres\JsonValue;

final class SharedStateRowMapper
{
    /**
     * @param array<string, mixed> $row
     */
    public static function toModel(array $row): SharedState
    {
        return new SharedState(
            stateId: (string)$row['state_id'],
            payload: JsonValue::decode((string)$row['payload_json']),
            createdAt: new DateTimeImmutable((string)$row['created_at']),
            ownerId: isset($row['owner_id']) ? (string)$row['owner_id'] : null
        );
    }
}
