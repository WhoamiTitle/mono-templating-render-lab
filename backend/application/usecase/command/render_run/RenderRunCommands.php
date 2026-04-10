<?php

declare(strict_types=1);

namespace application\usecase\command\render_run;

final readonly class StartRenderRunCommand
{
    /**
     * @param array<string, mixed> $contextJson
     */
    public function __construct(
        public string $actorId,
        public string $templateId,
        public array $contextJson
    ) {
    }
}

final readonly class CompleteRenderRunSuccessCommand
{
    public function __construct(
        public string $actorId,
        public string $runId,
        public ?int $durationMs,
        public string $outputText
    ) {
    }
}

final readonly class CompleteRenderRunFailureCommand
{
    public function __construct(
        public string $actorId,
        public string $runId,
        public ?int $durationMs,
        public ?string $errorCode,
        public ?string $errorMessage
    ) {
    }
}
