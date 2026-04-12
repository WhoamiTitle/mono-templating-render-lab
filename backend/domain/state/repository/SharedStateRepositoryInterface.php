<?php

declare(strict_types=1);

namespace domain\state\repository;

use domain\state\model\SharedState;

interface SharedStateRepositoryInterface
{
    public function save(SharedState $state): void;

    public function getById(string $stateId): ?SharedState;
}
