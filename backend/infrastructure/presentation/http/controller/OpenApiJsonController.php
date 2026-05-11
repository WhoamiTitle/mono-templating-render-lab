<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\controller;

use infrastructure\presentation\http\attribute\Route;
use infrastructure\presentation\http\HttpRequest;
use infrastructure\presentation\http\HttpResponse;
use infrastructure\presentation\http\JsonResponse;
use infrastructure\presentation\http\openapi\OpenApiDocumentFactory;

#[Route('GET', '/openapi.json')]
final readonly class OpenApiJsonController
{
    public function __construct(
        private OpenApiDocumentFactory $factory
    ) {
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        return JsonResponse::ok($this->factory->create());
    }
}
