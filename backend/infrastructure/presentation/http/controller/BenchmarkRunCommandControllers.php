<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\controller;

use application\usecase\command\benchmark_run\CompleteBenchmarkRunFailureCommand;
use application\usecase\command\benchmark_run\CompleteBenchmarkRunFailureUseCaseInterface;
use application\usecase\command\benchmark_run\CompleteBenchmarkRunSuccessCommand;
use application\usecase\command\benchmark_run\CompleteBenchmarkRunSuccessUseCaseInterface;
use application\usecase\command\benchmark_run\StartBenchmarkRunCommand;
use application\usecase\command\benchmark_run\StartBenchmarkRunUseCaseInterface;
use infrastructure\presentation\http\attribute\OpenApi;
use infrastructure\presentation\http\attribute\Route;
use infrastructure\presentation\http\HttpRequest;
use infrastructure\presentation\http\HttpResponse;
use infrastructure\presentation\http\JsonResponse;

#[Route('POST', '/benchmark-runs')]
#[OpenApi('Start benchmark run', ['Benchmark runs'], requestBody: 'StartBenchmarkRunRequest', response: 'StartBenchmarkRunResponse', responseStatus: 201)]
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

#[Route('POST', '/benchmark-runs/{benchmarkRunId}/success')]
#[OpenApi('Complete benchmark run successfully', ['Benchmark runs'], requestBody: 'CompleteBenchmarkRunSuccessRequest', response: 'CompleteBenchmarkRunResponse')]
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
            minMs: $this->requireFloat($payload, 'minMs'),
            maxMs: $this->requireFloat($payload, 'maxMs'),
            p95Ms: $this->requireFloat($payload, 'p95Ms'),
            outputBytes: $this->optionalInt($payload, 'outputBytes')
        ));

        return JsonResponse::ok($result->toArray());
    }

    /**
     * @param array<string, mixed> $payload
     * @return float[]
     */
    private function requireSamples(array $payload): array
    {
        $samples = $this->requireArray($payload, 'samplesMs');
        $normalized = [];
        foreach ($samples as $sample) {
            if (!is_int($sample) && !is_float($sample)) {
                throw new \infrastructure\presentation\http\exception\BadRequestHttpException(
                    'request.field.invalid_number_array',
                    ['field' => 'samplesMs']
                );
            }
            $normalized[] = (float)$sample;
        }

        return $normalized;
    }
}

#[Route('POST', '/benchmark-runs/{benchmarkRunId}/failure')]
#[OpenApi('Complete benchmark run with failure', ['Benchmark runs'], requestBody: 'CompleteBenchmarkRunResponse')]
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
