<?php

declare(strict_types=1);

namespace application\usecase\command\benchmark_run;

use application\usecase\command\Contract\CommandResultInterface;

interface StartBenchmarkRunUseCaseInterface
{
    public function execute(StartBenchmarkRunCommand $command): StartBenchmarkRunResult;
}

interface CompleteBenchmarkRunSuccessUseCaseInterface
{
    public function execute(CompleteBenchmarkRunSuccessCommand $command): CompleteBenchmarkRunResult;
}

interface CompleteBenchmarkRunFailureUseCaseInterface
{
    public function execute(CompleteBenchmarkRunFailureCommand $command): CompleteBenchmarkRunResult;
}

final readonly class StartBenchmarkRunResult implements CommandResultInterface
{
    public function __construct(
        public string $benchmarkRunId,
        public string $templateId,
        public string $ownerId,
        public string $status,
        public int $iterationsN,
        public string $startedAt
    ) {
    }

    public function toArray(): array
    {
        return [
            'benchmarkRunId' => $this->benchmarkRunId,
            'templateId' => $this->templateId,
            'ownerId' => $this->ownerId,
            'status' => $this->status,
            'iterationsN' => $this->iterationsN,
            'startedAt' => $this->startedAt,
        ];
    }
}

final readonly class CompleteBenchmarkRunResult implements CommandResultInterface
{
    public function __construct(
        public string $benchmarkRunId,
        public string $status,
        public string $finishedAt
    ) {
    }

    public function toArray(): array
    {
        return [
            'benchmarkRunId' => $this->benchmarkRunId,
            'status' => $this->status,
            'finishedAt' => $this->finishedAt,
        ];
    }
}
