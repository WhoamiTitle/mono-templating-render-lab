<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres\mapper;

use DateTimeImmutable;
use domain\account\model\PasswordResetToken;

final class PasswordResetTokenRowMapper
{
    /**
     * @param array<string, mixed> $row
     */
    public static function toModel(array $row): PasswordResetToken
    {
        return new PasswordResetToken(
            tokenId: (string)$row['token_id'],
            userId: (string)$row['user_id'],
            tokenHash: (string)$row['token_hash'],
            issuedAt: new DateTimeImmutable((string)$row['issued_at']),
            expiresAt: new DateTimeImmutable((string)$row['expires_at']),
            usedAt: $row['used_at'] !== null ? new DateTimeImmutable((string)$row['used_at']) : null
        );
    }
}
