<?php

declare(strict_types=1);

namespace application\usecase\command\render_run;

use application\service\ClockInterface;
use application\usecase\exception\ResourceNotFoundException;
use application\usecase\support\IsoDateTime;
use domain\render_run\repository\RenderRunRepositoryInterface;

final class CompleteRenderRunSuccessUseCase implements CompleteRenderRunSuccessUseCaseInterface
{
    public function __construct(
        private readonly RenderRunRepositoryInterface $renderRunRepository,
        private readonly ClockInterface $clock
    ) {
    }

    public function execute(CompleteRenderRunSuccessCommand $command): CompleteRenderRunResult
    {
        $renderRun = $this->renderRunRepository->getByIdForOwner($command->runId, $command->actorId);
        if ($renderRun === null) {
            throw new ResourceNotFoundException('render_run.not_found: ' . $command->runId);
        }

        $finishedAt = $this->clock->now();
        $renderRun->completeSuccess(
            finishedAt: $finishedAt,
            durationMs: $command->durationMs,
            outputText: $command->outputText
        );
        $this->renderRunRepository->save($renderRun);

        return new CompleteRenderRunResult(
            runId: $renderRun->runId,
            status: $renderRun->status,
            finishedAt: IsoDateTime::format($renderRun->finishedAt ?? $finishedAt),
            durationMs: $renderRun->durationMs
        );
    }
}
