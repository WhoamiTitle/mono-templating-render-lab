<?php

declare(strict_types=1);

namespace application\usecase\query\benchmark_run;

use application\usecase\exception\ResourceNotFoundException;
use application\usecase\support\IsoDateTime;
use domain\benchmark_run\model\BenchmarkRun;
use domain\benchmark_run\repository\BenchmarkRunRepositoryInterface;

final class GetBenchmarkRunUseCase implements GetBenchmarkRunUseCaseInterface
{
    public function __construct(
        private readonly BenchmarkRunRepositoryInterface $benchmarkRunRepository
    ) {
    }

    public function execute(GetBenchmarkRunQuery $query): BenchmarkRunView
    {
        $benchmarkRun = $this->benchmarkRunRepository->getByIdForOwner($query->benchmarkRunId, $query->actorId);
        if ($benchmarkRun === null) {
            throw new ResourceNotFoundException('benchmark_run.not_found: ' . $query->benchmarkRunId);
        }

        return BenchmarkRunViewFactory::fromModel($benchmarkRun);
    }
}

final class ListBenchmarkRunsUseCase implements ListBenchmarkRunsUseCaseInterface
{
    public function __construct(
        private readonly BenchmarkRunRepositoryInterface $benchmarkRunRepository
    ) {
    }

    public function execute(ListBenchmarkRunsQuery $query): array
    {
        $benchmarkRuns = $this->benchmarkRunRepository->listByOwner($query->actorId, $query->filters);

        return array_map(
            static fn (BenchmarkRun $benchmarkRun): BenchmarkRunView => BenchmarkRunViewFactory::fromModel($benchmarkRun),
            $benchmarkRuns
        );
    }
}

final class BenchmarkRunViewFactory
{
    public static function fromModel(BenchmarkRun $benchmarkRun): BenchmarkRunView
    {
        return new BenchmarkRunView(
            benchmarkRunId: $benchmarkRun->benchmarkRunId,
            ownerId: $benchmarkRun->ownerId,
            templateId: $benchmarkRun->templateId,
            engineType: $benchmarkRun->engineType,
            contextJson: $benchmarkRun->contextJson,
            iterationsN: $benchmarkRun->iterationsN,
            startedAt: IsoDateTime::format($benchmarkRun->startedAt),
            finishedAt: $benchmarkRun->finishedAt !== null ? IsoDateTime::format($benchmarkRun->finishedAt) : null,
            status: $benchmarkRun->status,
            samplesMs: $benchmarkRun->samplesMs,
            avgMs: $benchmarkRun->avgMs,
            minMs: $benchmarkRun->minMs,
            maxMs: $benchmarkRun->maxMs,
            p95Ms: $benchmarkRun->p95Ms,
            outputBytes: $benchmarkRun->outputBytes,
            errorCode: $benchmarkRun->errorCode,
            errorMessage: $benchmarkRun->errorMessage
        );
    }
}
