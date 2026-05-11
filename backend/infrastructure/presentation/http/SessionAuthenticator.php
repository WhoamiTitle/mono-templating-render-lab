<?php

declare(strict_types=1);

namespace infrastructure\presentation\http;

use application\service\ClockInterface;
use domain\account\repository\AuthSessionRepositoryInterface;
use infrastructure\presentation\http\attribute\OpenApi;
use infrastructure\presentation\http\exception\UnauthorizedHttpException;
use ReflectionClass;

final class SessionAuthenticator
{
    public function __construct(
        private readonly AuthSessionRepositoryInterface $authSessionRepository,
        private readonly ClockInterface $clock
    ) {
    }

    public function authenticate(HttpRequest $request, object $controller): HttpRequest
    {
        if (!$this->requiresSession($controller)) {
            return $request;
        }

        $sessionId = $request->cookie('session_id');
        if ($sessionId === null || trim($sessionId) === '') {
            throw new UnauthorizedHttpException('auth.session_id.required');
        }

        $session = $this->authSessionRepository->getById(trim($sessionId));
        if ($session === null) {
            throw new UnauthorizedHttpException('auth.session.not_found');
        }

        $session->assertActive($this->clock->now());

        return new HttpRequest(
            method: $request->method,
            path: $request->path,
            headers: $request->headers,
            queryParams: $request->queryParams,
            cookies: $request->cookies,
            routeParams: $request->routeParams,
            attributes: [...$request->attributes, 'actorId' => $session->userId],
            body: $request->body
        );
    }

    private function requiresSession(object $controller): bool
    {
        $attributes = (new ReflectionClass($controller))->getAttributes(OpenApi::class);
        if ($attributes === []) {
            return false;
        }

        $openApi = $attributes[0]->newInstance();
        if (!$openApi instanceof OpenApi) {
            return false;
        }

        return in_array('sessionCookie', $openApi->security, true);
    }
}
