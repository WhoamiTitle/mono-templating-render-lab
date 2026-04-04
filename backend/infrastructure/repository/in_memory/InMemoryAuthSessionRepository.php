<?php

declare(strict_types=1);

namespace infrastructure\repository\in_memory;

use domain\account\model\AuthSession;
use domain\account\repository\AuthSessionRepositoryInterface;

final class InMemoryAuthSessionRepository implements AuthSessionRepositoryInterface
{
    /**
     * @var array<string, AuthSession>
     */
    private array $sessionsById = [];

    public function save(AuthSession $session): void
    {
        $this->sessionsById[$session->sessionId] = clone $session;
    }

    public function getById(string $sessionId): ?AuthSession
    {
        $session = $this->sessionsById[$sessionId] ?? null;

        return $session !== null ? clone $session : null;
    }

    public function listActiveByUserId(string $userId): array
    {
        $result = [];

        foreach ($this->sessionsById as $session) {
            if ($session->userId !== $userId || $session->isRevoked) {
                continue;
            }

            $result[] = clone $session;
        }

        return $result;
    }
}
