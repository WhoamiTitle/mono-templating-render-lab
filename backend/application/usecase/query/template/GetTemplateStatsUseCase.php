<?php

declare(strict_types=1);

namespace application\usecase\query\template;

use application\usecase\exception\ResourceNotFoundException;
use domain\render_run\repository\RenderRunRepositoryInterface;
use domain\render_run\value_object\RenderStatus;
use domain\template\repository\TemplateRepositoryInterface;

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
