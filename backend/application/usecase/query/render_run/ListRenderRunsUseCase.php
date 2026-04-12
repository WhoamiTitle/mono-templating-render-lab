<?php

declare(strict_types=1);

namespace application\usecase\query\render_run;

use domain\render_run\model\RenderRun;
use domain\render_run\repository\RenderRunRepositoryInterface;

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
