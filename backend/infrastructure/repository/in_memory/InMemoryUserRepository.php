<?php

declare(strict_types=1);

namespace infrastructure\repository\in_memory;

use domain\account\model\User;
use domain\account\repository\UserRepositoryInterface;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    /**
     * @var array<string, User>
     */
    private array $usersById = [];

    /**
     * @var array<string, string>
     */
    private array $userIdsByEmail = [];

    public function save(User $user): void
    {
        $storedUser = clone $user;

        $previousUser = $this->usersById[$storedUser->userId] ?? null;
        if ($previousUser !== null && $previousUser->email !== $storedUser->email) {
            unset($this->userIdsByEmail[$previousUser->email]);
        }

        $this->usersById[$storedUser->userId] = $storedUser;
        $this->userIdsByEmail[$storedUser->email] = $storedUser->userId;
    }

    public function getById(string $userId): ?User
    {
        $user = $this->usersById[$userId] ?? null;

        return $user !== null ? clone $user : null;
    }

    public function getByEmail(string $email): ?User
    {
        $userId = $this->userIdsByEmail[$email] ?? null;
        if ($userId === null) {
            return null;
        }

        $user = $this->usersById[$userId] ?? null;

        return $user !== null ? clone $user : null;
    }
}
