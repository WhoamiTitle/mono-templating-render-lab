<?php

declare(strict_types=1);

namespace domain\account\repository;

use domain\account\model\User;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function getById(string $userId): ?User;

    public function getByEmail(string $email): ?User;
}
