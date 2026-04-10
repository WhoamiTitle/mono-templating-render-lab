<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\controller;

use application\usecase\command\template\DeactivateTemplateCommand;
use application\usecase\command\template\DeactivateTemplateUseCaseInterface;
use application\usecase\command\template\RegisterTemplateCommand;
use application\usecase\command\template\RegisterTemplateUseCaseInterface;
use application\usecase\command\template\UpdateTemplateBodyCommand;
use application\usecase\command\template\UpdateTemplateBodyUseCaseInterface;
use infrastructure\presentation\http\HttpRequest;
use infrastructure\presentation\http\HttpResponse;
use infrastructure\presentation\http\JsonResponse;

final class RegisterTemplateController extends AbstractJsonController
{
    public function __construct(
        private readonly RegisterTemplateUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $payload = $this->body($request);
        $result = $this->useCase->execute(new RegisterTemplateCommand(
            actorId: $this->requireActorId($request),
            name: $this->requireString($payload, 'name'),
            engineType: $this->requireString($payload, 'engineType'),
            templateBody: $this->requireString($payload, 'templateBody')
        ));

        return JsonResponse::created($result->toArray());
    }
}

final class UpdateTemplateBodyController extends AbstractJsonController
{
    public function __construct(
        private readonly UpdateTemplateBodyUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $payload = $this->body($request);
        $result = $this->useCase->execute(new UpdateTemplateBodyCommand(
            actorId: $this->requireActorId($request),
            templateId: $this->requireRouteParam($request, 'templateId'),
            templateBody: $this->requireString($payload, 'templateBody')
        ));

        return JsonResponse::ok($result->toArray());
    }
}

final class DeactivateTemplateController extends AbstractJsonController
{
    public function __construct(
        private readonly DeactivateTemplateUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $result = $this->useCase->execute(new DeactivateTemplateCommand(
            actorId: $this->requireActorId($request),
            templateId: $this->requireRouteParam($request, 'templateId')
        ));

        return JsonResponse::ok($result->toArray());
    }
}
