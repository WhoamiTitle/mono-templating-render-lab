<?php

declare(strict_types=1);

namespace domain\state\model;

use DateTimeImmutable;
use domain\common\exception\ValidationException;

final class SharedState
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string $stateId,
        public array $payload,
        public DateTimeImmutable $createdAt,
        public ?string $ownerId = null
    ) {
        $this->stateId = trim($this->stateId);
        $this->ownerId = $this->ownerId !== null ? trim($this->ownerId) : null;

        if ($this->stateId === '') {
            throw new ValidationException('state.id.empty', 4701);
        }

        if ($this->ownerId !== null && $this->ownerId === '') {
            throw new ValidationException('state.owner_id.empty: ' . $this->stateId, 4702);
        }

        $this->assertPayload($this->payload);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function create(
        string $stateId,
        array $payload,
        DateTimeImmutable $createdAt,
        ?string $ownerId = null
    ): self {
        return new self(
            stateId: $stateId,
            payload: $payload,
            createdAt: $createdAt,
            ownerId: $ownerId
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function assertPayload(array $payload): void
    {
        $slotA = $payload['slotA'] ?? null;
        $slotB = $payload['slotB'] ?? null;
        $json = $payload['json'] ?? null;

        if (!is_array($slotA)) {
            throw new ValidationException('state.payload.slot_a.invalid: ' . $this->stateId, 4703);
        }

        if (!is_array($slotB)) {
            throw new ValidationException('state.payload.slot_b.invalid: ' . $this->stateId, 4704);
        }

        if (!is_string($json)) {
            throw new ValidationException('state.payload.json.invalid: ' . $this->stateId, 4705);
        }

        $slotAEngineId = $slotA['engineId'] ?? null;
        $slotACode = $slotA['code'] ?? null;
        if (!is_string($slotAEngineId) || trim($slotAEngineId) === '') {
            throw new ValidationException('state.payload.slot_a.engine_id.invalid: ' . $this->stateId, 4706);
        }
        if (!is_string($slotACode)) {
            throw new ValidationException('state.payload.slot_a.code.invalid: ' . $this->stateId, 4707);
        }

        $slotBEngineId = $slotB['engineId'] ?? null;
        $slotBCode = $slotB['code'] ?? null;
        if (!is_string($slotBEngineId) || trim($slotBEngineId) === '') {
            throw new ValidationException('state.payload.slot_b.engine_id.invalid: ' . $this->stateId, 4708);
        }
        if (!is_string($slotBCode)) {
            throw new ValidationException('state.payload.slot_b.code.invalid: ' . $this->stateId, 4709);
        }
    }
}
