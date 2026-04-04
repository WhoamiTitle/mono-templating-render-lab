<?php

declare(strict_types=1);

namespace domain\account\repository;

use domain\account\model\AuthSession;

interface AuthSessionRepositoryInterface
{
    public function save(AuthSession $session): void;

    public function getById(string $sessionId): ?AuthSession;

    /**
     * @return AuthSession[]
     */
    public function listActiveByUserId(string $userId): array;
}
