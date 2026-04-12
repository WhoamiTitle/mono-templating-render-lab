<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\controller;

use application\usecase\query\state\GetStateQuery;
use application\usecase\query\state\GetStateUseCaseInterface;
use infrastructure\presentation\http\HttpRequest;
use infrastructure\presentation\http\HttpResponse;
use infrastructure\presentation\http\JsonResponse;

final class GetStateController extends AbstractJsonController
{
    public function __construct(
        private readonly GetStateUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $result = $this->useCase->execute(new GetStateQuery(
            stateId: $this->requireRouteParam($request, 'stateId')
        ));

        return JsonResponse::ok($result->toArray());
    }
}
