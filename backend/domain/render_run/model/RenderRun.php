<?php

declare(strict_types=1);

namespace domain\render_run\model;

use DateTimeImmutable;
use domain\common\exception\ValidationException;
use domain\render_run\exception\RenderRunAlreadyFinishedException;
use domain\render_run\value_object\RenderStatus;

class RenderRun
{
    public function __construct(
        public string $runId,
        public string $templateId,
        public string $ownerId,
        public string $engineType,
        public string $templateBodySnapshot,
        public array $contextJson,
        public DateTimeImmutable $startedAt,
        public ?DateTimeImmutable $finishedAt = null,
        public string $status = RenderStatus::IN_PROGRESS,
        public ?int $durationMs = null,
        public ?string $outputText = null,
        public ?string $errorCode = null,
        public ?string $errorMessage = null
    ) {
        $this->runId = trim($this->runId);
        $this->templateId = trim($this->templateId);
        $this->ownerId = trim($this->ownerId);
        $this->engineType = trim($this->engineType);
        $this->templateBodySnapshot = trim($this->templateBodySnapshot);
        $this->status = RenderStatus::from($this->status)->value();

        $this->assertIdentity();
        $this->assertSnapshot();
        $this->assertDuration($this->durationMs);
    }

    public static function start(
        string $runId,
        string $templateId,
        string $ownerId,
        string $engineType,
        string $templateBodySnapshot,
        array $contextJson,
        DateTimeImmutable $startedAt
    ): self {
        return new self(
            runId: $runId,
            templateId: $templateId,
            ownerId: $ownerId,
            engineType: $engineType,
            templateBodySnapshot: $templateBodySnapshot,
            contextJson: $contextJson,
            startedAt: $startedAt
        );
    }

    public function completeSuccess(DateTimeImmutable $finishedAt, ?int $durationMs, string $outputText): void
    {
        $this->assertNotFinished();

        $outputText = trim($outputText);
        if ($outputText === '') {
            throw new ValidationException('render_run.output.empty: ' . $this->runId, 4302);
        }

        $this->assertFinishedAt($finishedAt);

        $resolvedDuration = $durationMs ?? $this->calculateDurationMs($finishedAt);
        $this->assertDuration($resolvedDuration);

        $this->finishedAt = $finishedAt;
        $this->durationMs = $resolvedDuration;
        $this->status = RenderStatus::SUCCESS;
        $this->outputText = $outputText;
        $this->errorCode = null;
        $this->errorMessage = null;
    }

    public function completeFailure(
        DateTimeImmutable $finishedAt,
        ?int $durationMs,
        ?string $errorCode,
        ?string $errorMessage
    ): void {
        $this->assertNotFinished();
        $this->assertFinishedAt($finishedAt);

        $errorCode = $errorCode !== null ? trim($errorCode) : null;
        $errorMessage = $errorMessage !== null ? trim($errorMessage) : null;

        if (($errorCode ?? '') === '' && ($errorMessage ?? '') === '') {
            throw new ValidationException('render_run.error.missing: ' . $this->runId, 4303);
        }

        $resolvedDuration = $durationMs ?? $this->calculateDurationMs($finishedAt);
        $this->assertDuration($resolvedDuration);

        $this->finishedAt = $finishedAt;
        $this->durationMs = $resolvedDuration;
        $this->status = RenderStatus::FAILED;
        $this->outputText = null;
        $this->errorCode = $errorCode !== '' ? $errorCode : null;
        $this->errorMessage = $errorMessage !== '' ? $errorMessage : null;
    }

    public function isFinished(): bool
    {
        return $this->status !== RenderStatus::IN_PROGRESS;
    }

    private function assertIdentity(): void
    {
        if ($this->runId === '') {
            throw new ValidationException('render_run.id.empty', 4304);
        }

        if ($this->templateId === '') {
            throw new ValidationException('render_run.template_id.empty', 4305);
        }

        if ($this->ownerId === '') {
            throw new ValidationException('render_run.owner_id.empty: ' . $this->runId, 4309);
        }
    }

    private function assertSnapshot(): void
    {
        if ($this->templateBodySnapshot === '') {
            throw new ValidationException('render_run.snapshot.empty: ' . $this->runId, 4306);
        }
    }

    private function assertNotFinished(): void
    {
        if ($this->isFinished()) {
            throw new RenderRunAlreadyFinishedException($this->runId);
        }
    }

    private function assertFinishedAt(DateTimeImmutable $finishedAt): void
    {
        if ($finishedAt < $this->startedAt) {
            throw new ValidationException('render_run.finished_at.invalid: ' . $this->runId, 4307);
        }
    }

    private function assertDuration(?int $durationMs): void
    {
        if ($durationMs !== null && $durationMs < 0) {
            throw new ValidationException('render_run.duration.negative: ' . $this->runId, 4308);
        }
    }

    private function calculateDurationMs(DateTimeImmutable $finishedAt): int
    {
        $secondsDelta = (float)$finishedAt->format('U.u') - (float)$this->startedAt->format('U.u');
        $durationMs = (int)round($secondsDelta * 1000);

        return max($durationMs, 0);
    }
}

