<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\controller;

use application\usecase\command\benchmark_run\CompleteBenchmarkRunFailureCommand;
use application\usecase\command\benchmark_run\CompleteBenchmarkRunFailureUseCaseInterface;
use application\usecase\command\benchmark_run\CompleteBenchmarkRunSuccessCommand;
use application\usecase\command\benchmark_run\CompleteBenchmarkRunSuccessUseCaseInterface;
use application\usecase\command\benchmark_run\StartBenchmarkRunCommand;
use application\usecase\command\benchmark_run\StartBenchmarkRunUseCaseInterface;
use infrastructure\presentation\http\HttpRequest;
use infrastructure\presentation\http\HttpResponse;
use infrastructure\presentation\http\JsonResponse;

final class StartBenchmarkRunController extends AbstractJsonController
{
    public function __construct(
        private readonly StartBenchmarkRunUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $payload = $this->body($request);
        $result = $this->useCase->execute(new StartBenchmarkRunCommand(
            actorId: $this->requireActorId($request),
            templateId: $this->requireString($payload, 'templateId'),
            contextJson: $this->requireArray($payload, 'context'),
            iterationsN: $this->requireInt($payload, 'iterationsN')
        ));

        return JsonResponse::created($result->toArray());
    }
}

final class CompleteBenchmarkRunSuccessController extends AbstractJsonController
{
    public function __construct(
        private readonly CompleteBenchmarkRunSuccessUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $payload = $this->body($request);
        $result = $this->useCase->execute(new CompleteBenchmarkRunSuccessCommand(
            actorId: $this->requireActorId($request),
            benchmarkRunId: $this->requireRouteParam($request, 'benchmarkRunId'),
            samplesMs: $this->requireSamples($payload),
            avgMs: $this->requireFloat($payload, 'avgMs'),
            minMs: $this->requireInt($payload, 'minMs'),
            maxMs: $this->requireInt($payload, 'maxMs'),
            p95Ms: $this->requireInt($payload, 'p95Ms'),
            outputBytes: $this->optionalInt($payload, 'outputBytes')
        ));

        return JsonResponse::ok($result->toArray());
    }

    /**
     * @param array<string, mixed> $payload
     * @return int[]
     */
    private function requireSamples(array $payload): array
    {
        $samples = $this->requireArray($payload, 'samplesMs');
        foreach ($samples as $sample) {
            if (!is_int($sample)) {
                throw new \infrastructure\presentation\http\exception\BadRequestHttpException(
                    'request.field.invalid_int_array',
                    ['field' => 'samplesMs']
                );
            }
        }

        return $samples;
    }
}

final class CompleteBenchmarkRunFailureController extends AbstractJsonController
{
    public function __construct(
        private readonly CompleteBenchmarkRunFailureUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $payload = $this->body($request);
        $result = $this->useCase->execute(new CompleteBenchmarkRunFailureCommand(
            actorId: $this->requireActorId($request),
            benchmarkRunId: $this->requireRouteParam($request, 'benchmarkRunId'),
            errorCode: $this->optionalString($payload, 'errorCode'),
            errorMessage: $this->optionalString($payload, 'errorMessage')
        ));

        return JsonResponse::ok($result->toArray());
    }
}
