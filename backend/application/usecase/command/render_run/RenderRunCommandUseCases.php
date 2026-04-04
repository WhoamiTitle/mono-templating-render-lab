<?php

declare(strict_types=1);

namespace application\usecase\command\render_run;

use application\usecase\command\Contract\CommandResultInterface;

interface StartRenderRunUseCaseInterface
{
    public function execute(StartRenderRunCommand $command): StartRenderRunResult;
}

interface CompleteRenderRunSuccessUseCaseInterface
{
    public function execute(CompleteRenderRunSuccessCommand $command): CompleteRenderRunResult;
}

interface CompleteRenderRunFailureUseCaseInterface
{
    public function execute(CompleteRenderRunFailureCommand $command): CompleteRenderRunResult;
}

final readonly class StartRenderRunResult implements CommandResultInterface
{
    public function __construct(
        public string $runId,
        public string $templateId,
        public string $ownerId,
        public string $status,
        public string $startedAt
    ) {
    }

    public function toArray(): array
    {
        return [
            'runId' => $this->runId,
            'templateId' => $this->templateId,
            'ownerId' => $this->ownerId,
            'status' => $this->status,
            'startedAt' => $this->startedAt,
        ];
    }
}

final readonly class CompleteRenderRunResult implements CommandResultInterface
{
    public function __construct(
        public string $runId,
        public string $status,
        public string $finishedAt,
        public ?int $durationMs
    ) {
    }

    public function toArray(): array
    {
        return [
            'runId' => $this->runId,
            'status' => $this->status,
            'finishedAt' => $this->finishedAt,
            'durationMs' => $this->durationMs,
        ];
    }
}
