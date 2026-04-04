<?php

declare(strict_types=1);

namespace domain\account\model;

use DateTimeImmutable;
use domain\account\exception\PasswordResetTokenAlreadyUsedException;
use domain\account\exception\PasswordResetTokenExpiredException;
use domain\common\exception\ValidationException;

class PasswordResetToken
{
    public function __construct(
        public string $tokenId,
        public string $userId,
        public string $tokenHash,
        public DateTimeImmutable $issuedAt,
        public DateTimeImmutable $expiresAt,
        public ?DateTimeImmutable $usedAt = null
    ) {
        $this->tokenId = trim($this->tokenId);
        $this->userId = trim($this->userId);
        $this->tokenHash = trim($this->tokenHash);

        if ($this->tokenId === '') {
            throw new ValidationException('account.password_reset_token.id.empty', 4423);
        }

        if ($this->userId === '') {
            throw new ValidationException('account.password_reset_token.user_id.empty: ' . $this->tokenId, 4424);
        }

        if ($this->tokenHash === '') {
            throw new ValidationException('account.password_reset_token.hash.empty: ' . $this->tokenId, 4425);
        }

        if ($this->expiresAt <= $this->issuedAt) {
            throw new ValidationException('account.password_reset_token.expires_at.invalid: ' . $this->tokenId, 4426);
        }

        if ($this->usedAt !== null && $this->usedAt < $this->issuedAt) {
            throw new ValidationException('account.password_reset_token.used_at.invalid: ' . $this->tokenId, 4427);
        }
    }

    public static function issue(
        string $tokenId,
        string $userId,
        string $tokenHash,
        DateTimeImmutable $issuedAt,
        DateTimeImmutable $expiresAt
    ): self {
        return new self(
            tokenId: $tokenId,
            userId: $userId,
            tokenHash: $tokenHash,
            issuedAt: $issuedAt,
            expiresAt: $expiresAt,
            usedAt: null
        );
    }

    public function isUsed(): bool
    {
        return $this->usedAt !== null;
    }

    public function isExpiredAt(DateTimeImmutable $at): bool
    {
        return $at >= $this->expiresAt;
    }

    public function assertCanBeUsedAt(DateTimeImmutable $at): void
    {
        if ($this->isUsed()) {
            throw new PasswordResetTokenAlreadyUsedException($this->tokenId);
        }

        if ($this->isExpiredAt($at)) {
            throw new PasswordResetTokenExpiredException($this->tokenId);
        }
    }

    public function use(DateTimeImmutable $usedAt): void
    {
        $this->assertCanBeUsedAt($usedAt);

        if ($usedAt < $this->issuedAt) {
            throw new ValidationException('account.password_reset_token.used_at.invalid: ' . $this->tokenId, 4427);
        }

        $this->usedAt = $usedAt;
    }
}

