<?php

declare(strict_types=1);

namespace application\usecase\command\account;

use application\service\ClockInterface;
use application\service\IdGeneratorInterface;
use application\service\PasswordHasherInterface;
use application\usecase\support\IsoDateTime;
use DateInterval;
use domain\account\exception\AuthenticationFailedException;
use domain\account\model\AuthSession;
use domain\account\repository\AuthSessionRepositoryInterface;
use domain\account\repository\UserRepositoryInterface;
use domain\account\value_object\Email;

final class LoginUserUseCase implements LoginUserUseCaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly AuthSessionRepositoryInterface $authSessionRepository,
        private readonly PasswordHasherInterface $passwordHasher,
        private readonly IdGeneratorInterface $idGenerator,
        private readonly ClockInterface $clock,
        private readonly DateInterval $sessionTtl
    ) {
    }

    public function execute(LoginUserCommand $command): LoginUserResult
    {
        $email = Email::from($command->email)->value();
        $user = $this->userRepository->getByEmail($email);
        if ($user === null || !$this->passwordHasher->verify($command->password, $user->passwordHash)) {
            throw new AuthenticationFailedException($email);
        }

        $user->assertCanAuthenticate();

        $issuedAt = $this->clock->now();
        $expiresAt = $issuedAt->add($this->sessionTtl);
        $session = AuthSession::start(
            sessionId: $this->idGenerator->generate(),
            userId: $user->userId,
            issuedAt: $issuedAt,
            expiresAt: $expiresAt
        );

        $user->markLoggedIn($issuedAt);

        $this->authSessionRepository->save($session);
        $this->userRepository->save($user);

        return new LoginUserResult(
            sessionId: $session->sessionId,
            userId: $user->userId,
            expiresAt: IsoDateTime::format($session->expiresAt)
        );
    }
}
