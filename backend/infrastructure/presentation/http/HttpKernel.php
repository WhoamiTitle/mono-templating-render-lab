<?php

declare(strict_types=1);

namespace infrastructure\presentation\http;

use infrastructure\presentation\http\route\Router;

final class HttpKernel
{
    public function __construct(
        private readonly Router $router,
        private readonly HttpExceptionResponder $exceptionResponder = new HttpExceptionResponder()
    ) {
    }

    public function handle(HttpRequest $request): HttpResponse
    {
        try {
            $routeMatch = $this->router->match($request->method, $request->path);
            $request = new HttpRequest(
                method: $request->method,
                path: $request->path,
                headers: $request->headers,
                queryParams: $request->queryParams,
                cookies: $request->cookies,
                routeParams: $routeMatch->routeParams,
                attributes: $request->attributes,
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
