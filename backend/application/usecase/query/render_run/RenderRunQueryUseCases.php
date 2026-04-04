<?php

declare(strict_types=1);

namespace application\usecase\query\render_run;

interface GetRenderRunUseCaseInterface
{
    public function execute(GetRenderRunQuery $query): RenderRunView;
}

interface ListRenderRunsUseCaseInterface
{
    /**
     * @return RenderRunView[]
     */
    public function execute(ListRenderRunsQuery $query): array;
}

interface GetRecentFailuresUseCaseInterface
{
    /**
     * @return RenderRunView[]
     */
    public function execute(GetRecentFailuresQuery $query): array;
}

final readonly class GetRenderRunQuery
{
    public function __construct(
        public string $actorId,
        public string $runId
    ) {
    }
}

final readonly class ListRenderRunsQuery
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

final readonly class GetRecentFailuresQuery
{
    public function __construct(
        public string $actorId,
        public int $limit = 10
    ) {
    }
}

final readonly class RenderRunView
{
    /**
     * @param array<string, mixed> $contextJson
     */
    public function __construct(
        public string $runId,
        public string $templateId,
        public string $ownerId,
        public string $engineType,
        public string $templateBodySnapshot,
        public array $contextJson,
        public string $startedAt,
        public ?string $finishedAt,
        public string $status,
        public ?int $durationMs,
        public ?string $outputText,
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
            'runId' => $this->runId,
            'templateId' => $this->templateId,
            'ownerId' => $this->ownerId,
            'engineType' => $this->engineType,
            'templateBodySnapshot' => $this->templateBodySnapshot,
            'context' => $this->contextJson,
            'startedAt' => $this->startedAt,
            'finishedAt' => $this->finishedAt,
            'status' => $this->status,
            'durationMs' => $this->durationMs,
            'outputText' => $this->outputText,
            'errorCode' => $this->errorCode,
            'errorMessage' => $this->errorMessage,
        ];
    }
}
