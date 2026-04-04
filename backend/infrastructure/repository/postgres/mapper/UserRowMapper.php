<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres\mapper;

use DateTimeImmutable;
use domain\account\model\User;

final class UserRowMapper
{
    /**
     * @param array<string, mixed> $row
     */
    public static function toModel(array $row): User
    {
        return new User(
            userId: (string)$row['user_id'],
            email: (string)$row['email'],
            passwordHash: (string)$row['password_hash'],
            status: (string)$row['status'],
            createdAt: new DateTimeImmutable((string)$row['created_at']),
            updatedAt: new DateTimeImmutable((string)$row['updated_at']),
            lastLoginAt: $row['last_login_at'] !== null ? new DateTimeImmutable((string)$row['last_login_at']) : null
        );
    }
}
