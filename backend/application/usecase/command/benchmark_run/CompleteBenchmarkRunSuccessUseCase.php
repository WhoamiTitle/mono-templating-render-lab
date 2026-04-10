<?php

declare(strict_types=1);

namespace application\usecase\command\benchmark_run;

use application\service\ClockInterface;
use application\usecase\exception\ResourceNotFoundException;
use application\usecase\support\IsoDateTime;
use domain\benchmark_run\repository\BenchmarkRunRepositoryInterface;

final class CompleteBenchmarkRunSuccessUseCase implements CompleteBenchmarkRunSuccessUseCaseInterface
{
    public function __construct(
        private readonly BenchmarkRunRepositoryInterface $benchmarkRunRepository,
        private readonly ClockInterface $clock
    ) {
    }

    public function execute(CompleteBenchmarkRunSuccessCommand $command): CompleteBenchmarkRunResult
    {
        $benchmarkRun = $this->benchmarkRunRepository->getByIdForOwner($command->benchmarkRunId, $command->actorId);
        if ($benchmarkRun === null) {
            throw new ResourceNotFoundException('benchmark_run.not_found: ' . $command->benchmarkRunId);
        }

        $finishedAt = $this->clock->now();
        $benchmarkRun->completeSuccess(
            finishedAt: $finishedAt,
            samplesMs: $command->samplesMs,
            avgMs: $command->avgMs,
            minMs: $command->minMs,
            maxMs: $command->maxMs,
            p95Ms: $command->p95Ms,
            outputBytes: $command->outputBytes
        );
        $this->benchmarkRunRepository->save($benchmarkRun);

        return new CompleteBenchmarkRunResult(
            benchmarkRunId: $benchmarkRun->benchmarkRunId,
            status: $benchmarkRun->status,
            finishedAt: IsoDateTime::format($benchmarkRun->finishedAt ?? $finishedAt)
        );
    }
}
