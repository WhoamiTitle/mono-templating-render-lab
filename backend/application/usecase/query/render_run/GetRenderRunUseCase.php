<?php

declare(strict_types=1);

namespace application\usecase\query\render_run;

use application\usecase\exception\ResourceNotFoundException;
use domain\render_run\repository\RenderRunRepositoryInterface;

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
