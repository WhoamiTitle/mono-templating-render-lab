<?php

declare(strict_types=1);

namespace application\usecase\query\render_run;

use domain\render_run\model\RenderRun;
use domain\render_run\repository\RenderRunRepositoryInterface;
use domain\render_run\value_object\RenderStatus;

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
