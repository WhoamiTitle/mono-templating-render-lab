<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\controller;

use application\usecase\command\render_run\CompleteRenderRunFailureCommand;
use application\usecase\command\render_run\CompleteRenderRunFailureUseCaseInterface;
use application\usecase\command\render_run\CompleteRenderRunSuccessCommand;
use application\usecase\command\render_run\CompleteRenderRunSuccessUseCaseInterface;
use application\usecase\command\render_run\StartRenderRunCommand;
use application\usecase\command\render_run\StartRenderRunUseCaseInterface;
use infrastructure\presentation\http\HttpRequest;
use infrastructure\presentation\http\HttpResponse;
use infrastructure\presentation\http\JsonResponse;

final class RenderRunCommandControllers extends AbstractJsonController
{
    public function __construct(
        private readonly StartRenderRunUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $payload = $this->body($request);
        $result = $this->useCase->execute(new StartRenderRunCommand(
            actorId: $this->requireActorId($request),
            templateId: $this->requireString($payload, 'templateId'),
            contextJson: $this->requireArray($payload, 'context')
        ));

        return JsonResponse::created($result->toArray());
    }
}

final class CompleteRenderRunSuccessController extends AbstractJsonController
{
    public function __construct(
        private readonly CompleteRenderRunSuccessUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $payload = $this->body($request);
        $result = $this->useCase->execute(new CompleteRenderRunSuccessCommand(
            actorId: $this->requireActorId($request),
            runId: $this->requireRouteParam($request, 'runId'),
            durationMs: $this->optionalInt($payload, 'durationMs'),
            outputText: $this->requireString($payload, 'outputText')
        ));

        return JsonResponse::ok($result->toArray());
    }
}

final class CompleteRenderRunFailureController extends AbstractJsonController
{
    public function __construct(
        private readonly CompleteRenderRunFailureUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $payload = $this->body($request);
        $result = $this->useCase->execute(new CompleteRenderRunFailureCommand(
            actorId: $this->requireActorId($request),
            runId: $this->requireRouteParam($request, 'runId'),
            durationMs: $this->optionalInt($payload, 'durationMs'),
            errorCode: $this->optionalString($payload, 'errorCode'),
            errorMessage: $this->optionalString($payload, 'errorMessage')
        ));

        return JsonResponse::ok($result->toArray());
    }
}
