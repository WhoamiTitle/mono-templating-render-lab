<?php

declare(strict_types=1);

namespace application\usecase\query\benchmark_run;

interface GetBenchmarkRunUseCaseInterface
{
    public function execute(GetBenchmarkRunQuery $query): BenchmarkRunView;
}

interface ListBenchmarkRunsUseCaseInterface
{
    /**
     * @return BenchmarkRunView[]
     */
    public function execute(ListBenchmarkRunsQuery $query): array;
}

final readonly class GetBenchmarkRunQuery
{
    public function __construct(
        public string $actorId,
        public string $benchmarkRunId
    ) {
    }
}

final readonly class ListBenchmarkRunsQuery
{
    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(
        public string $actorId,
        public array $filters = []
    ) {
    }
}

final readonly class BenchmarkRunView
{
    /**
     * @param array<string, mixed> $contextJson
     * @param int[] $samplesMs
     */
    public function __construct(
        public string $benchmarkRunId,
        public string $ownerId,
        public string $templateId,
        public string $engineType,
        public array $contextJson,
        public int $iterationsN,
        public string $startedAt,
        public ?string $finishedAt,
        public string $status,
        public array $samplesMs,
        public ?float $avgMs,
        public ?int $minMs,
        public ?int $maxMs,
        public ?int $p95Ms,
        public ?int $outputBytes,
        public ?string $errorCode,
        public ?string $errorMessage
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'benchmarkRunId' => $this->benchmarkRunId,
            'ownerId' => $this->ownerId,
            'templateId' => $this->templateId,
            'engineType' => $this->engineType,
            'context' => $this->contextJson,
            'iterationsN' => $this->iterationsN,
            'startedAt' => $this->startedAt,
            'finishedAt' => $this->finishedAt,
            'status' => $this->status,
            'samplesMs' => $this->samplesMs,
            'avgMs' => $this->avgMs,
            'minMs' => $this->minMs,
            'maxMs' => $this->maxMs,
            'p95Ms' => $this->p95Ms,
            'outputBytes' => $this->outputBytes,
            'errorCode' => $this->errorCode,
            'errorMessage' => $this->errorMessage,
        ];
    }
}
