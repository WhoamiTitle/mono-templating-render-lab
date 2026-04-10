<?php

declare(strict_types=1);

namespace application\usecase\query\template;

use application\usecase\exception\ResourceNotFoundException;
use application\usecase\support\IsoDateTime;
use domain\render_run\repository\RenderRunRepositoryInterface;
use domain\render_run\value_object\RenderStatus;
use domain\template\model\Template;
use domain\template\repository\TemplateRepositoryInterface;

final class GetTemplateUseCase implements GetTemplateUseCaseInterface
{
    public function __construct(
        private readonly TemplateRepositoryInterface $templateRepository
    ) {
    }

    public function execute(GetTemplateQuery $query): TemplateView
    {
        $template = $this->templateRepository->getByIdForOwner($query->templateId, $query->actorId);
        if ($template === null) {
            throw new ResourceNotFoundException('template.not_found: ' . $query->templateId);
        }

        return TemplateViewFactory::fromModel($template);
    }
}

final class ListTemplatesUseCase implements ListTemplatesUseCaseInterface
{
    public function __construct(
        private readonly TemplateRepositoryInterface $templateRepository
    ) {
    }

    public function execute(ListTemplatesQuery $query): array
    {
        $templates = $this->templateRepository->listByOwner($query->actorId, $query->filters);

        return array_map(
            static fn (Template $template): TemplateView => TemplateViewFactory::fromModel($template),
            $templates
        );
    }
}

final class GetTemplateStatsUseCase implements GetTemplateStatsUseCaseInterface
{
    public function __construct(
        private readonly TemplateRepositoryInterface $templateRepository,
        private readonly RenderRunRepositoryInterface $renderRunRepository
    ) {
    }

    public function execute(GetTemplateStatsQuery $query): TemplateStatsView
    {
        $template = $this->templateRepository->getByIdForOwner($query->templateId, $query->actorId);
        if ($template === null) {
            throw new ResourceNotFoundException('template.not_found: ' . $query->templateId);
        }

        $runs = $this->renderRunRepository->listByOwner(
            $query->actorId,
            ['templateId' => $query->templateId]
        );

        $totalRuns = count($runs);
        $successRuns = 0;
        $failedRuns = 0;
        $durations = [];

        foreach ($runs as $run) {
            if ($run->status === RenderStatus::SUCCESS) {
                $successRuns++;
            }

            if ($run->status === RenderStatus::FAILED) {
                $failedRuns++;
            }

            if ($run->durationMs !== null) {
                $durations[] = $run->durationMs;
            }
        }

        $avgDuration = $durations === [] ? null : array_sum($durations) / count($durations);

        return new TemplateStatsView(
            templateId: $template->templateId,
            totalRuns: $totalRuns,
            successRuns: $successRuns,
            failedRuns: $failedRuns,
            avgDurationMs: $avgDuration,
            minDurationMs: $durations === [] ? null : min($durations),
            maxDurationMs: $durations === [] ? null : max($durations)
        );
    }
}

final class TemplateViewFactory
{
    public static function fromModel(Template $template): TemplateView
    {
        return new TemplateView(
            templateId: $template->templateId,
            ownerId: $template->ownerId,
            name: $template->name,
            engineType: $template->engineType,
            templateBody: $template->templateBody,
            isActive: $template->isActive,
            createdAt: IsoDateTime::format($template->createdAt),
            updatedAt: IsoDateTime::format($template->updatedAt)
        );
    }
}
