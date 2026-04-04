<?php

declare(strict_types=1);

namespace application\usecase\command\account;

use application\usecase\command\Contract\CommandResultInterface;

interface RegisterUserUseCaseInterface
{
    public function execute(RegisterUserCommand $command): RegisterUserResult;
}

interface LoginUserUseCaseInterface
{
    public function execute(LoginUserCommand $command): LoginUserResult;
}

interface LogoutUserUseCaseInterface
{
    public function execute(LogoutUserCommand $command): LogoutUserResult;
}

final readonly class RegisterUserResult implements CommandResultInterface
{
    public function __construct(
        public string $userId,
        public string $email,
        public string $createdAt
    ) {
    }

    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'email' => $this->email,
            'createdAt' => $this->createdAt,
        ];
    }
}

final readonly class LoginUserResult implements CommandResultInterface
{
    public function __construct(
        public string $sessionId,
        public string $userId,
        public string $expiresAt
    ) {
    }

    public function toArray(): array
    {
        return [
            'sessionId' => $this->sessionId,
            'userId' => $this->userId,
            'expiresAt' => $this->expiresAt,
        ];
    }
}

final readonly class LogoutUserResult implements CommandResultInterface
{
    public function __construct(
        public string $sessionId
    ) {
    }

    public function toArray(): array
    {
        return [
            'sessionId' => $this->sessionId,
        ];
    }
}
