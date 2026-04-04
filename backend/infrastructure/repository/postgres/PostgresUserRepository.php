<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres;

use domain\account\model\User;
use domain\account\repository\UserRepositoryInterface;
use infrastructure\repository\postgres\mapper\UserRowMapper;
use PDO;

final class PostgresUserRepository extends PostgresRepository implements UserRepositoryInterface
{
    public function __construct(PDO $connection)
    {
        parent::__construct($connection);
    }

    public function save(User $user): void
    {
        $this->execute(
            <<<SQL
            INSERT INTO users (
                user_id,
                email,
                password_hash,
                status,
                created_at,
                updated_at,
                last_login_at
            ) VALUES (
                :user_id,
                :email,
                :password_hash,
                :status,
                :created_at,
                :updated_at,
                :last_login_at
            )
            ON CONFLICT (user_id) DO UPDATE SET
                email = EXCLUDED.email,
                password_hash = EXCLUDED.password_hash,
                status = EXCLUDED.status,
                created_at = EXCLUDED.created_at,
                updated_at = EXCLUDED.updated_at,
                last_login_at = EXCLUDED.last_login_at
            SQL,
            [
                'user_id' => $user->userId,
                'email' => $user->email,
                'password_hash' => $user->passwordHash,
                'status' => $user->status,
                'created_at' => $user->createdAt,
                'updated_at' => $user->updatedAt,
                'last_login_at' => $user->lastLoginAt,
            ]
        );
    }

    public function getById(string $userId): ?User
    {
        $row = $this->fetchOne(
            'SELECT * FROM users WHERE user_id = :user_id',
            ['user_id' => $userId]
        );

        return $row !== null ? UserRowMapper::toModel($row) : null;
    }

    public function getByEmail(string $email): ?User
    {
        $row = $this->fetchOne(
            'SELECT * FROM users WHERE email = :email',
            ['email' => $email]
        );

        return $row !== null ? UserRowMapper::toModel($row) : null;
    }
}
