<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres\mapper;

use DateTimeImmutable;
use domain\account\model\AuthSession;

final class AuthSessionRowMapper
{
    /**
     * @param array<string, mixed> $row
     */
    public static function toModel(array $row): AuthSession
    {
        return new AuthSession(
            sessionId: (string)$row['session_id'],
            userId: (string)$row['user_id'],
            issuedAt: new DateTimeImmutable((string)$row['issued_at']),
            expiresAt: new DateTimeImmutable((string)$row['expires_at']),
            isRevoked: (bool)$row['is_revoked'],
            revokedAt: $row['revoked_at'] !== null ? new DateTimeImmutable((string)$row['revoked_at']) : null
        );
    }
}
