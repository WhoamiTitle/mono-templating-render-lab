<?php

declare(strict_types=1);

namespace application\usecase\command\account;

final readonly class RegisterUserCommand
{
    public function __construct(
        public string $email,
        public string $password
    ) {
    }
}

final readonly class LoginUserCommand
{
    public function __construct(
        public string $email,
        public string $password
    ) {
    }
}

final readonly class LogoutUserCommand
{
    public function __construct(
        public string $actorId,
        public string $sessionId
    ) {
    }
}
