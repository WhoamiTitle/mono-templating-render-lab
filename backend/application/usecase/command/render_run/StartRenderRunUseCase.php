<?php

declare(strict_types=1);

namespace application\usecase\command\render_run;

use application\service\ClockInterface;
use application\service\IdGeneratorInterface;
use application\usecase\exception\ResourceNotFoundException;
use application\usecase\support\IsoDateTime;
use domain\render_run\repository\RenderRunRepositoryInterface;
use domain\template\repository\TemplateRepositoryInterface;

final class StartRenderRunUseCase implements StartRenderRunUseCaseInterface
{
    public function __construct(
        private readonly TemplateRepositoryInterface $templateRepository,
        private readonly RenderRunRepositoryInterface $renderRunRepository,
        private readonly IdGeneratorInterface $idGenerator,
        private readonly ClockInterface $clock
    ) {
    }

    public function execute(StartRenderRunCommand $command): StartRenderRunResult
    {
        $template = $this->templateRepository->getByIdForOwner($command->templateId, $command->actorId);
        if ($template === null) {
            throw new ResourceNotFoundException('template.not_found: ' . $command->templateId);
        }

        $renderRun = $template->startRenderRun(
            actorId: $command->actorId,
            runId: $this->idGenerator->generate(),
            contextJson: $command->contextJson,
            startedAt: $this->clock->now()
        );

        $this->renderRunRepository->save($renderRun);

        return new StartRenderRunResult(
            runId: $renderRun->runId,
            templateId: $renderRun->templateId,
            ownerId: $renderRun->ownerId,
            status: $renderRun->status,
            startedAt: IsoDateTime::format($renderRun->startedAt)
        );
    }
}
