<?php

declare(strict_types=1);

namespace domain\benchmark_run\model;

use DateTimeImmutable;
use domain\benchmark_run\exception\BenchmarkRunAlreadyFinishedException;
use domain\benchmark_run\value_object\BenchmarkStatus;
use domain\common\exception\ValidationException;
use domain\template\value_object\EngineType;

class BenchmarkRun
{
    public function __construct(
        public string $benchmarkRunId,
        public string $ownerId,
        public string $templateId,
        public string $engineType,
        public array $contextJson,
        public int $iterationsN,
        public DateTimeImmutable $startedAt,
        public ?DateTimeImmutable $finishedAt = null,
        public string $status = BenchmarkStatus::IN_PROGRESS,
        public array $samplesMs = [],
        public ?float $avgMs = null,
        public ?int $minMs = null,
        public ?int $maxMs = null,
        public ?int $p95Ms = null,
        public ?int $outputBytes = null,
        public ?string $errorCode = null,
        public ?string $errorMessage = null
    ) {
        $this->benchmarkRunId = trim($this->benchmarkRunId);
        $this->ownerId = trim($this->ownerId);
        $this->templateId = trim($this->templateId);
        $this->engineType = EngineType::from($this->engineType)->value();
        $this->status = BenchmarkStatus::from($this->status)->value();

        $this->assertIdentity();
        $this->assertIterations();
        $this->assertOutputBytes($this->outputBytes);
        $this->samplesMs = self::normalizeSamples($this->samplesMs, $this->benchmarkRunId);

        $this->assertStateConsistency();
    }

    public static function start(
        string $benchmarkRunId,
        string $ownerId,
        string $templateId,
        string $engineType,
        array $contextJson,
        int $iterationsN,
        DateTimeImmutable $startedAt
    ): self {
        return new self(
            benchmarkRunId: $benchmarkRunId,
            ownerId: $ownerId,
            templateId: $templateId,
            engineType: $engineType,
            contextJson: $contextJson,
            iterationsN: $iterationsN,
            startedAt: $startedAt
        );
    }

    public function completeSuccess(
        DateTimeImmutable $finishedAt,
        array $samplesMs,
        float $avgMs,
        int $minMs,
        int $maxMs,
        int $p95Ms,
        ?int $outputBytes = null
    ): void {
        $this->assertNotFinished();
        $this->assertFinishedAt($finishedAt);

        $samples = self::normalizeSamples($samplesMs, $this->benchmarkRunId);
        if (count($samples) !== $this->iterationsN) {
            throw new ValidationException(
                'benchmark_run.samples.count_mismatch: ' . $this->benchmarkRunId,
                4602
            );
        }

        $this->assertOutputBytes($outputBytes);
        $this->assertSummary($avgMs, $minMs, $maxMs, $p95Ms);

        $this->finishedAt = $finishedAt;
        $this->status = BenchmarkStatus::SUCCESS;
        $this->samplesMs = $samples;
        $this->avgMs = $avgMs;
        $this->minMs = $minMs;
        $this->maxMs = $maxMs;
        $this->p95Ms = $p95Ms;
        $this->outputBytes = $outputBytes;
        $this->errorCode = null;
        $this->errorMessage = null;
    }

    public function completeFailure(
        DateTimeImmutable $finishedAt,
        ?string $errorCode,
        ?string $errorMessage
    ): void {
        $this->assertNotFinished();
        $this->assertFinishedAt($finishedAt);

        $errorCode = $errorCode !== null ? trim($errorCode) : null;
        $errorMessage = $errorMessage !== null ? trim($errorMessage) : null;
        if (($errorCode ?? '') === '' && ($errorMessage ?? '') === '') {
            throw new ValidationException(
                'benchmark_run.error.missing: ' . $this->benchmarkRunId,
                4603
            );
        }

        $this->finishedAt = $finishedAt;
        $this->status = BenchmarkStatus::FAILED;
        $this->samplesMs = [];
        $this->avgMs = null;
        $this->minMs = null;
        $this->maxMs = null;
        $this->p95Ms = null;
        $this->outputBytes = null;
        $this->errorCode = $errorCode !== '' ? $errorCode : null;
        $this->errorMessage = $errorMessage !== '' ? $errorMessage : null;
    }

    public function isFinished(): bool
    {
        return $this->status !== BenchmarkStatus::IN_PROGRESS;
    }

    private function assertIdentity(): void
    {
        if ($this->benchmarkRunId === '') {
            throw new ValidationException('benchmark_run.id.empty', 4604);
        }

        if ($this->ownerId === '') {
            throw new ValidationException('benchmark_run.owner_id.empty: ' . $this->benchmarkRunId, 4605);
        }

        if ($this->templateId === '') {
            throw new ValidationException('benchmark_run.template_id.empty: ' . $this->benchmarkRunId, 4606);
        }
    }

    private function assertIterations(): void
    {
        if ($this->iterationsN < 1) {
            throw new ValidationException('benchmark_run.iterations.invalid: ' . $this->benchmarkRunId, 4607);
        }
    }

    private function assertOutputBytes(?int $outputBytes): void
    {
        if ($outputBytes !== null && $outputBytes < 0) {
            throw new ValidationException('benchmark_run.output_bytes.negative: ' . $this->benchmarkRunId, 4608);
        }
    }

    private function assertNotFinished(): void
    {
        if ($this->isFinished()) {
            throw new BenchmarkRunAlreadyFinishedException($this->benchmarkRunId);
        }
    }

    private function assertFinishedAt(DateTimeImmutable $finishedAt): void
    {
        if ($finishedAt < $this->startedAt) {
            throw new ValidationException('benchmark_run.finished_at.invalid: ' . $this->benchmarkRunId, 4609);
        }
    }

    private function assertStateConsistency(): void
    {
        if ($this->status === BenchmarkStatus::IN_PROGRESS) {
            return;
        }

        if ($this->finishedAt === null) {
            throw new ValidationException('benchmark_run.finished_at.missing: ' . $this->benchmarkRunId, 4610);
        }

        if ($this->status === BenchmarkStatus::SUCCESS) {
            if (
                count($this->samplesMs) !== $this->iterationsN
                || $this->avgMs === null
                || $this->minMs === null
                || $this->maxMs === null
                || $this->p95Ms === null
            ) {
                throw new ValidationException('benchmark_run.summary.missing: ' . $this->benchmarkRunId, 4611);
            }
            $this->assertSummary($this->avgMs, $this->minMs, $this->maxMs, $this->p95Ms);
            return;
        }

        if (($this->errorCode ?? '') === '' && ($this->errorMessage ?? '') === '') {
            throw new ValidationException('benchmark_run.error.missing: ' . $this->benchmarkRunId, 4603);
        }
    }

    private static function normalizeSamples(array $samples, string $benchmarkRunId): array
    {
        $normalized = [];
        foreach ($samples as $sample) {
            if (!is_int($sample)) {
                throw new ValidationException('benchmark_run.sample.not_int: ' . $benchmarkRunId, 4612);
            }

            if ($sample < 0) {
                throw new ValidationException('benchmark_run.sample.negative: ' . $benchmarkRunId, 4613);
            }

            $normalized[] = $sample;
        }

        return $normalized;
    }

    private function assertSummary(float $avgMs, int $minMs, int $maxMs, int $p95Ms): void
    {
        if ($avgMs < 0) {
            throw new ValidationException('benchmark_run.avg.negative: ' . $this->benchmarkRunId, 4614);
        }

        if ($minMs < 0 || $maxMs < 0 || $p95Ms < 0) {
            throw new ValidationException('benchmark_run.summary.negative: ' . $this->benchmarkRunId, 4615);
        }

        if ($minMs > $maxMs) {
            throw new ValidationException('benchmark_run.summary.min_gt_max: ' . $this->benchmarkRunId, 4616);
        }

        if ($p95Ms < $minMs || $p95Ms > $maxMs) {
            throw new ValidationException('benchmark_run.summary.p95_out_of_range: ' . $this->benchmarkRunId, 4617);
        }

        if ($avgMs < $minMs || $avgMs > $maxMs) {
            throw new ValidationException('benchmark_run.summary.avg_out_of_range: ' . $this->benchmarkRunId, 4618);
        }
    }
}
