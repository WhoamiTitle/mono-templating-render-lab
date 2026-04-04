<?php

declare(strict_types=1);

namespace domain\account\model;

use DateTimeImmutable;
use domain\account\exception\UserBlockedException;
use domain\account\value_object\Email;
use domain\account\value_object\UserStatus;
use domain\common\exception\ValidationException;

class User
{
    public function __construct(
        public string $userId,
        public string $email,
        public string $passwordHash,
        public string $status,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
        public ?DateTimeImmutable $lastLoginAt = null
    ) {
        $this->userId = trim($this->userId);
        $this->email = Email::from($this->email)->value();
        $this->passwordHash = trim($this->passwordHash);
        $this->status = UserStatus::from($this->status)->value();

        if ($this->userId === '') {
            throw new ValidationException('account.user_id.empty', 4413);
        }

        if ($this->passwordHash === '') {
            throw new ValidationException('account.password_hash.empty: ' . $this->userId, 4414);
        }

        if ($this->updatedAt < $this->createdAt) {
            throw new ValidationException('account.updated_at.invalid: ' . $this->userId, 4415);
        }
    }

    public static function register(
        string $userId,
        string $email,
        string $passwordHash,
        DateTimeImmutable $createdAt
    ): self {
        return new self(
            userId: $userId,
            email: Email::from($email)->value(),
            passwordHash: $passwordHash,
            status: UserStatus::ACTIVE,
            createdAt: $createdAt,
            updatedAt: $createdAt,
            lastLoginAt: null
        );
    }

    public function changePassword(string $passwordHash, DateTimeImmutable $updatedAt): void
    {
        $passwordHash = trim($passwordHash);
        if ($passwordHash === '') {
            throw new ValidationException('account.password_hash.empty: ' . $this->userId, 4414);
        }

        if ($updatedAt < $this->updatedAt) {
            throw new ValidationException('account.updated_at.invalid: ' . $this->userId, 4415);
        }

        $this->passwordHash = $passwordHash;
        $this->updatedAt = $updatedAt;
    }

    public function markLoggedIn(DateTimeImmutable $loggedInAt): void
    {
        if ($this->status === UserStatus::BLOCKED) {
            throw new UserBlockedException($this->userId);
        }

        if ($loggedInAt < $this->updatedAt) {
            throw new ValidationException('account.updated_at.invalid: ' . $this->userId, 4415);
        }

        $this->lastLoginAt = $loggedInAt;
        $this->updatedAt = $loggedInAt;
    }

    public function assertCanAuthenticate(): void
    {
        if ($this->status === UserStatus::BLOCKED) {
            throw new UserBlockedException($this->userId);
        }
    }
}
