<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres;

use domain\account\model\AuthSession;
use domain\account\repository\AuthSessionRepositoryInterface;
use infrastructure\repository\postgres\mapper\AuthSessionRowMapper;
use PDO;

final class PostgresAuthSessionRepository extends PostgresRepository implements AuthSessionRepositoryInterface
{
    public function __construct(PDO $connection)
    {
        parent::__construct($connection);
    }

    public function save(AuthSession $session): void
    {
        $this->execute(
            <<<SQL
            INSERT INTO auth_sessions (
                session_id,
                user_id,
                issued_at,
                expires_at,
                is_revoked,
                revoked_at
            ) VALUES (
                :session_id,
                :user_id,
                :issued_at,
                :expires_at,
                :is_revoked,
                :revoked_at
            )
            ON CONFLICT (session_id) DO UPDATE SET
                user_id = EXCLUDED.user_id,
                issued_at = EXCLUDED.issued_at,
                expires_at = EXCLUDED.expires_at,
                is_revoked = EXCLUDED.is_revoked,
                revoked_at = EXCLUDED.revoked_at
            SQL,
            [
                'session_id' => $session->sessionId,
                'user_id' => $session->userId,
                'issued_at' => $session->issuedAt,
                'expires_at' => $session->expiresAt,
                'is_revoked' => $session->isRevoked,
                'revoked_at' => $session->revokedAt,
            ]
        );
    }

    public function getById(string $sessionId): ?AuthSession
    {
        $row = $this->fetchOne(
            'SELECT * FROM auth_sessions WHERE session_id = :session_id',
            ['session_id' => $sessionId]
        );

        return $row !== null ? AuthSessionRowMapper::toModel($row) : null;
    }

    public function listActiveByUserId(string $userId): array
    {
        $rows = $this->fetchAll(
            <<<SQL
            SELECT *
            FROM auth_sessions
            WHERE user_id = :user_id
              AND is_revoked = false
              AND expires_at > CURRENT_TIMESTAMP
            ORDER BY issued_at DESC, session_id ASC
            SQL,
            ['user_id' => $userId]
        );

        return array_map(static fn (array $row): AuthSession => AuthSessionRowMapper::toModel($row), $rows);
    }
}
