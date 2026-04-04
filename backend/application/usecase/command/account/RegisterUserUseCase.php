<?php

declare(strict_types=1);

namespace application\usecase\command\account;

use application\service\ClockInterface;
use application\service\IdGeneratorInterface;
use application\service\PasswordHasherInterface;
use application\usecase\exception\ConflictException;
use application\usecase\support\IsoDateTime;
use domain\account\model\User;
use domain\account\repository\UserRepositoryInterface;
use domain\account\value_object\Email;

final class RegisterUserUseCase implements RegisterUserUseCaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly IdGeneratorInterface $idGenerator,
        private readonly PasswordHasherInterface $passwordHasher,
        private readonly ClockInterface $clock
    ) {
    }

    public function execute(RegisterUserCommand $command): RegisterUserResult
    {
        $email = Email::from($command->email)->value();
        if ($this->userRepository->getByEmail($email) !== null) {
            throw new ConflictException('account.user.email_already_registered: ' . $email);
        }

        $createdAt = $this->clock->now();
        $user = User::register(
            userId: $this->idGenerator->generate(),
            email: $email,
            passwordHash: $this->passwordHasher->hash($command->password),
            createdAt: $createdAt
        );

        $this->userRepository->save($user);

        return new RegisterUserResult(
            userId: $user->userId,
            email: $user->email,
            createdAt: IsoDateTime::format($user->createdAt)
        );
    }
}
