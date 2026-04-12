<?php

declare(strict_types=1);

namespace infrastructure\repository\in_memory;

use domain\state\model\SharedState;
use domain\state\repository\SharedStateRepositoryInterface;

final class InMemorySharedStateRepository implements SharedStateRepositoryInterface
{
    /**
     * @var array<string, SharedState>
     */
    private array $states = [];

    public function save(SharedState $state): void
    {
        $this->states[$state->stateId] = clone $state;
    }

    public function getById(string $stateId): ?SharedState
    {
        $state = $this->states[$stateId] ?? null;

        return $state !== null ? clone $state : null;
    }
}
