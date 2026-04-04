<?php

declare(strict_types=1);

namespace domain\account\model;

use DateTimeImmutable;
use domain\account\exception\AuthSessionExpiredException;
use domain\common\exception\ValidationException;

class AuthSession
{
    public function __construct(
        public string $sessionId,
        public string $userId,
        public DateTimeImmutable $issuedAt,
        public DateTimeImmutable $expiresAt,
        public bool $isRevoked = false,
        public ?DateTimeImmutable $revokedAt = null
    ) {
        $this->sessionId = trim($this->sessionId);
        $this->userId = trim($this->userId);

        if ($this->sessionId === '') {
            throw new ValidationException('account.session_id.empty', 4416);
        }

        if ($this->userId === '') {
            throw new ValidationException('account.session.user_id.empty: ' . $this->sessionId, 4417);
        }

        if ($this->expiresAt <= $this->issuedAt) {
            throw new ValidationException('account.session.expires_at.invalid: ' . $this->sessionId, 4418);
        }

        if ($this->isRevoked && $this->revokedAt === null) {
            throw new ValidationException('account.session.revoked_at.empty: ' . $this->sessionId, 4419);
        }
    }

    public static function start(
        string $sessionId,
        string $userId,
        DateTimeImmutable $issuedAt,
        DateTimeImmutable $expiresAt
    ): self {
        return new self(
            sessionId: $sessionId,
            userId: $userId,
            issuedAt: $issuedAt,
            expiresAt: $expiresAt,
            isRevoked: false,
            revokedAt: null
        );
    }

    public function revoke(DateTimeImmutable $revokedAt): void
    {
        if ($this->isRevoked) {
            return;
        }

        if ($revokedAt < $this->issuedAt) {
            throw new ValidationException('account.session.revoked_at.invalid: ' . $this->sessionId, 4420);
        }

        $this->isRevoked = true;
        $this->revokedAt = $revokedAt;
    }

    public function isActiveAt(DateTimeImmutable $at): bool
    {
        return !$this->isRevoked && $at < $this->expiresAt;
    }

    public function assertActive(DateTimeImmutable $at): void
    {
        if (!$this->isActiveAt($at)) {
            throw new AuthSessionExpiredException($this->sessionId);
        }
    }
}
