<?php

declare(strict_types=1);

namespace application\usecase\command\benchmark_run;

final readonly class StartBenchmarkRunCommand
{
    /**
     * @param array<string, mixed> $contextJson
     */
    public function __construct(
        public string $actorId,
        public string $templateId,
        public array $contextJson,
        public int $iterationsN
    ) {
    }
}

final readonly class CompleteBenchmarkRunSuccessCommand
{
    /**
     * @param int[] $samplesMs
     */
    public function __construct(
        public string $actorId,
        public string $benchmarkRunId,
        public array $samplesMs,
        public float $avgMs,
        public int $minMs,
        public int $maxMs,
        public int $p95Ms,
        public ?int $outputBytes
    ) {
    }
}

final readonly class CompleteBenchmarkRunFailureCommand
{
    public function __construct(
        public string $actorId,
        public string $benchmarkRunId,
        public ?string $errorCode,
        public ?string $errorMessage
    ) {
    }
}
