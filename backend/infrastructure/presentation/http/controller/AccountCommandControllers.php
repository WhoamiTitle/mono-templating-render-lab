<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\controller;

use application\usecase\command\account\LoginUserCommand;
use application\usecase\command\account\LoginUserUseCaseInterface;
use application\usecase\command\account\LogoutUserCommand;
use application\usecase\command\account\LogoutUserUseCaseInterface;
use application\usecase\command\account\RegisterUserCommand;
use application\usecase\command\account\RegisterUserUseCaseInterface;
use DateTimeImmutable;
use infrastructure\presentation\http\HttpRequest;
use infrastructure\presentation\http\HttpResponse;
use infrastructure\presentation\http\JsonResponse;
use infrastructure\presentation\http\SessionCookieFactory;
use infrastructure\presentation\http\exception\BadRequestHttpException;

final class RegisterUserController extends AbstractJsonController
{
    public function __construct(
        private readonly RegisterUserUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $payload = $this->body($request);
        $result = $this->useCase->execute(new RegisterUserCommand(
            email: $this->requireString($payload, 'email'),
            password: $this->requireString($payload, 'password')
        ));

        return JsonResponse::created($result->toArray());
    }
}

final class LoginUserController extends AbstractJsonController
{
    public function __construct(
        private readonly LoginUserUseCaseInterface $useCase,
        private readonly SessionCookieFactory $sessionCookieFactory = new SessionCookieFactory()
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $payload = $this->body($request);
        $result = $this->useCase->execute(new LoginUserCommand(
            email: $this->requireString($payload, 'email'),
            password: $this->requireString($payload, 'password')
        ));

        try {
            $expiresAt = new DateTimeImmutable($result->expiresAt);
        } catch (\Exception $exception) {
            throw new BadRequestHttpException('auth.session.expires_at.invalid', previous: $exception);
        }

        return JsonResponse::ok(
            $result->toArray(),
            [
                'set-cookie' => [
                    $this->sessionCookieFactory->issue($result->sessionId, $expiresAt),
                ],
            ]
        );
    }
}

final class LogoutUserController extends AbstractJsonController
{
    public function __construct(
        private readonly LogoutUserUseCaseInterface $useCase,
        private readonly SessionCookieFactory $sessionCookieFactory = new SessionCookieFactory()
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $this->useCase->execute(new LogoutUserCommand(
            actorId: $this->requireActorId($request),
            sessionId: $this->requireSessionId($request)
        ));

        return JsonResponse::noContent([
            'set-cookie' => [
                $this->sessionCookieFactory->expire(),
            ],
        ]);
    }
}
