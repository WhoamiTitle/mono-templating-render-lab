<?php

declare(strict_types=1);

namespace application\usecase\command\render_run;

use application\service\ClockInterface;
use application\usecase\exception\ResourceNotFoundException;
use application\usecase\support\IsoDateTime;
use domain\render_run\repository\RenderRunRepositoryInterface;

final class CompleteRenderRunFailureUseCase implements CompleteRenderRunFailureUseCaseInterface
{
    public function __construct(
        private readonly RenderRunRepositoryInterface $renderRunRepository,
        private readonly ClockInterface $clock
    ) {
    }

    public function execute(CompleteRenderRunFailureCommand $command): CompleteRenderRunResult
    {
        $renderRun = $this->renderRunRepository->getByIdForOwner($command->runId, $command->actorId);
        if ($renderRun === null) {
            throw new ResourceNotFoundException('render_run.not_found: ' . $command->runId);
        }

        $finishedAt = $this->clock->now();
        $renderRun->completeFailure(
            finishedAt: $finishedAt,
            durationMs: $command->durationMs,
            errorCode: $command->errorCode,
            errorMessage: $command->errorMessage
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
