<?php

declare(strict_types=1);

namespace application\usecase\query\render_run;

use application\usecase\exception\ResourceNotFoundException;
use application\usecase\support\IsoDateTime;
use domain\render_run\model\RenderRun;
use domain\render_run\repository\RenderRunRepositoryInterface;
use domain\render_run\value_object\RenderStatus;

final class GetRenderRunUseCase implements GetRenderRunUseCaseInterface
{
    public function __construct(
        private readonly RenderRunRepositoryInterface $renderRunRepository
    ) {
    }

    public function execute(GetRenderRunQuery $query): RenderRunView
    {
        $renderRun = $this->renderRunRepository->getByIdForOwner($query->runId, $query->actorId);
        if ($renderRun === null) {
            throw new ResourceNotFoundException('render_run.not_found: ' . $query->runId);
        }

        return RenderRunViewFactory::fromModel($renderRun);
    }
}

final class ListRenderRunsUseCase implements ListRenderRunsUseCaseInterface
{
    public function __construct(
        private readonly RenderRunRepositoryInterface $renderRunRepository
    ) {
    }

    public function execute(ListRenderRunsQuery $query): array
    {
        $renderRuns = $this->renderRunRepository->listByOwner($query->actorId, $query->filters);

        return array_map(
            static fn (RenderRun $renderRun): RenderRunView => RenderRunViewFactory::fromModel($renderRun),
            $renderRuns
        );
    }
}

final class GetRecentFailuresUseCase implements GetRecentFailuresUseCaseInterface
{
    public function __construct(
        private readonly RenderRunRepositoryInterface $renderRunRepository
    ) {
    }

    public function execute(GetRecentFailuresQuery $query): array
    {
        $renderRuns = $this->renderRunRepository->listByOwner(
            $query->actorId,
            ['status' => RenderStatus::FAILED]
        );

        $limitedRuns = array_slice($renderRuns, 0, max(1, $query->limit));

        return array_map(
            static fn (RenderRun $renderRun): RenderRunView => RenderRunViewFactory::fromModel($renderRun),
            $limitedRuns
        );
    }
}

final class RenderRunViewFactory
{
    public static function fromModel(RenderRun $renderRun): RenderRunView
    {
        return new RenderRunView(
            runId: $renderRun->runId,
            templateId: $renderRun->templateId,
            ownerId: $renderRun->ownerId,
            engineType: $renderRun->engineType,
            templateBodySnapshot: $renderRun->templateBodySnapshot,
            contextJson: $renderRun->contextJson,
            startedAt: IsoDateTime::format($renderRun->startedAt),
            finishedAt: $renderRun->finishedAt !== null ? IsoDateTime::format($renderRun->finishedAt) : null,
            status: $renderRun->status,
            durationMs: $renderRun->durationMs,
            outputText: $renderRun->outputText,
            errorCode: $renderRun->errorCode,
            errorMessage: $renderRun->errorMessage
        );
    }
}
