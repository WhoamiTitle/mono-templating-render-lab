<?php

declare(strict_types=1);

namespace infrastructure\presentation\http;

use application\service\ClockInterface;
use domain\account\repository\AuthSessionRepositoryInterface;
use infrastructure\presentation\http\exception\UnauthorizedHttpException;
use infrastructure\presentation\http\route\Router;

final class HttpKernel
{
    public function __construct(
        private readonly Router $router,
        private readonly AuthSessionRepositoryInterface $authSessionRepository,
        private readonly ClockInterface $clock,
        private readonly HttpExceptionResponder $exceptionResponder = new HttpExceptionResponder()
    ) {
    }

    public function handle(HttpRequest $request): HttpResponse
    {
        try {
            $routeMatch = $this->router->match($request->method, $request->path);
            $attributes = $request->attributes;

            if ($routeMatch->requiresAuth) {
                $sessionId = trim((string)($request->cookie('session_id') ?? ''));
                if ($sessionId === '') {
                    throw new UnauthorizedHttpException('auth.session_id.required');
                }

                $session = $this->authSessionRepository->getById($sessionId);
                if ($session === null) {
                    throw new UnauthorizedHttpException('auth.session.not_found');
                }

                $session->assertActive($this->clock->now());
                $attributes['actorId'] = $session->userId;
            }

            $request = new HttpRequest(
                method: $request->method,
                path: $request->path,
                headers: $request->headers,
                queryParams: $request->queryParams,
                cookies: $request->cookies,
                routeParams: $routeMatch->routeParams,
                attributes: $attributes,
                body: $request->body
            );

            $controller = $routeMatch->controller;
            $response = $controller($request);

            if (!$response instanceof HttpResponse) {
                throw new \RuntimeException('presentation.http.invalid_controller_response');
            }

            return $response;
        } catch (\Throwable $exception) {
            return $this->exceptionResponder->toResponse($exception);
        }
    }
}
