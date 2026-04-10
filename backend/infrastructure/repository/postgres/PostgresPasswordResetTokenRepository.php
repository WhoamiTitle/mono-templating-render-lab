<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres;

use domain\account\model\PasswordResetToken;
use domain\account\repository\PasswordResetTokenRepositoryInterface;
use infrastructure\repository\postgres\mapper\PasswordResetTokenRowMapper;
use PDO;

final class PostgresPasswordResetTokenRepository extends PostgresRepository implements PasswordResetTokenRepositoryInterface
{
    public function __construct(PDO $connection)
    {
        parent::__construct($connection);
    }

    public function save(PasswordResetToken $token): void
    {
        $this->execute(
            <<<SQL
            INSERT INTO password_reset_tokens (
                token_id,
                user_id,
                token_hash,
                issued_at,
                expires_at,
                used_at
            ) VALUES (
                :token_id,
                :user_id,
                :token_hash,
                :issued_at,
                :expires_at,
                :used_at
            )
            ON CONFLICT (token_id) DO UPDATE SET
                user_id = EXCLUDED.user_id,
                token_hash = EXCLUDED.token_hash,
                issued_at = EXCLUDED.issued_at,
                expires_at = EXCLUDED.expires_at,
                used_at = EXCLUDED.used_at
            SQL,
            [
                'token_id' => $token->tokenId,
                'user_id' => $token->userId,
                'token_hash' => $token->tokenHash,
                'issued_at' => $token->issuedAt,
                'expires_at' => $token->expiresAt,
                'used_at' => $token->usedAt,
            ]
        );
    }

    public function getById(string $tokenId): ?PasswordResetToken
    {
        $row = $this->fetchOne(
            'SELECT * FROM password_reset_tokens WHERE token_id = :token_id',
            ['token_id' => $tokenId]
        );

        return $row !== null ? PasswordResetTokenRowMapper::toModel($row) : null;
    }

    public function getByTokenHash(string $tokenHash): ?PasswordResetToken
    {
        $row = $this->fetchOne(
            'SELECT * FROM password_reset_tokens WHERE token_hash = :token_hash',
            ['token_hash' => $tokenHash]
        );

        return $row !== null ? PasswordResetTokenRowMapper::toModel($row) : null;
    }
}
