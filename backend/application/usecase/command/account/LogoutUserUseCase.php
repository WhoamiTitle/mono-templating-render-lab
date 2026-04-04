<?php

declare(strict_types=1);

namespace application\usecase\command\account;

use application\service\ClockInterface;
use application\usecase\exception\ResourceNotFoundException;
use domain\account\repository\AuthSessionRepositoryInterface;
use domain\common\exception\AccessDeniedException;

final class LogoutUserUseCase implements LogoutUserUseCaseInterface
{
    public function __construct(
        private readonly AuthSessionRepositoryInterface $authSessionRepository,
        private readonly ClockInterface $clock
    ) {
    }

    public function execute(LogoutUserCommand $command): LogoutUserResult
    {
        $session = $this->authSessionRepository->getById($command->sessionId);
        if ($session === null) {
            throw new ResourceNotFoundException('account.session.not_found: ' . $command->sessionId);
        }

        if ($session->userId !== trim($command->actorId)) {
            throw new AccessDeniedException('account.session.access_denied: ' . $command->sessionId, 4030);
        }

        $session->revoke($this->clock->now());
        $this->authSessionRepository->save($session);

        return new LogoutUserResult($session->sessionId);
    }
}
