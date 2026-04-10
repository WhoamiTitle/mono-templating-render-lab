<?php

declare(strict_types=1);

namespace application\usecase\query\template;

interface GetTemplateUseCaseInterface
{
    public function execute(GetTemplateQuery $query): TemplateView;
}

interface ListTemplatesUseCaseInterface
{
    /**
     * @return TemplateView[]
     */
    public function execute(ListTemplatesQuery $query): array;
}

interface GetTemplateStatsUseCaseInterface
{
    public function execute(GetTemplateStatsQuery $query): TemplateStatsView;
}

final readonly class GetTemplateQuery
{
    public function __construct(
        public string $actorId,
        public string $templateId
    ) {
    }
}

final readonly class ListTemplatesQuery
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

final readonly class GetTemplateStatsQuery
{
    public function __construct(
        public string $actorId,
        public string $templateId
    ) {
    }
}

final readonly class TemplateView
{
    public function __construct(
        public string $templateId,
        public string $ownerId,
        public string $name,
        public string $engineType,
        public string $templateBody,
        public bool $isActive,
        public string $createdAt,
        public string $updatedAt
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'templateId' => $this->templateId,
            'ownerId' => $this->ownerId,
            'name' => $this->name,
            'engineType' => $this->engineType,
            'templateBody' => $this->templateBody,
            'isActive' => $this->isActive,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}

final readonly class TemplateStatsView
{
    public function __construct(
        public string $templateId,
        public int $totalRuns,
        public int $successRuns,
        public int $failedRuns,
        public ?float $avgDurationMs,
        public ?int $minDurationMs,
        public ?int $maxDurationMs
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'templateId' => $this->templateId,
            'totalRuns' => $this->totalRuns,
            'successRuns' => $this->successRuns,
            'failedRuns' => $this->failedRuns,
            'avgDurationMs' => $this->avgDurationMs,
            'minDurationMs' => $this->minDurationMs,
            'maxDurationMs' => $this->maxDurationMs,
        ];
    }
}
