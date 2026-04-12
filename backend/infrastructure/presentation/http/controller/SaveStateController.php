<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\controller;

use application\usecase\command\state\SaveStateCommand;
use application\usecase\command\state\SaveStateUseCaseInterface;
use infrastructure\presentation\http\HttpRequest;
use infrastructure\presentation\http\HttpResponse;
use infrastructure\presentation\http\JsonResponse;

final class SaveStateController extends AbstractJsonController
{
    public function __construct(
        private readonly SaveStateUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $payload = $this->body($request);
        $result = $this->useCase->execute(new SaveStateCommand(
            state: $payload
        ));

        return JsonResponse::created($result->toArray());
    }
}
