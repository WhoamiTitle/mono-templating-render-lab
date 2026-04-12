<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres;

use domain\state\model\SharedState;
use domain\state\repository\SharedStateRepositoryInterface;
use infrastructure\repository\postgres\mapper\SharedStateRowMapper;
use PDO;

final class PostgresSharedStateRepository extends PostgresRepository implements SharedStateRepositoryInterface
{
    public function __construct(PDO $connection)
    {
        parent::__construct($connection);
    }

    public function save(SharedState $state): void
    {
        $this->execute(
            <<<SQL
            INSERT INTO shared_states (
                state_id,
                owner_id,
                payload_json,
                created_at
            ) VALUES (
                :state_id,
                :owner_id,
                :payload_json,
                :created_at
            )
            ON CONFLICT (state_id) DO UPDATE SET
                owner_id = EXCLUDED.owner_id,
                payload_json = EXCLUDED.payload_json,
                created_at = EXCLUDED.created_at
            SQL,
            [
                'state_id' => $state->stateId,
                'owner_id' => $state->ownerId,
                'payload_json' => JsonValue::encode($state->payload),
                'created_at' => $state->createdAt,
            ]
        );
    }

    public function getById(string $stateId): ?SharedState
    {
        $row = $this->fetchOne(
            'SELECT * FROM shared_states WHERE state_id = :state_id',
            ['state_id' => $stateId]
        );

        return $row !== null ? SharedStateRowMapper::toModel($row) : null;
    }
}
