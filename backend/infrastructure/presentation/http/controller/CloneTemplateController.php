<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\controller;

use application\usecase\command\template\CloneTemplateCommand;
use application\usecase\command\template\CloneTemplateUseCaseInterface;
use infrastructure\presentation\http\HttpRequest;
use infrastructure\presentation\http\HttpResponse;
use infrastructure\presentation\http\JsonResponse;

final class CloneTemplateController extends AbstractJsonController
{
    public function __construct(
        private readonly CloneTemplateUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $result = $this->useCase->execute(new CloneTemplateCommand(
            actorId: $this->requireActorId($request),
            templateId: $this->requireRouteParam($request, 'templateId')
        ));

        return JsonResponse::created($result->toArray());
    }
}
