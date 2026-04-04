<?php

declare(strict_types=1);

namespace domain\account\repository;

use domain\account\model\PasswordResetToken;

interface PasswordResetTokenRepositoryInterface
{
    public function save(PasswordResetToken $token): void;

    public function getById(string $tokenId): ?PasswordResetToken;

    public function getByTokenHash(string $tokenHash): ?PasswordResetToken;
}

