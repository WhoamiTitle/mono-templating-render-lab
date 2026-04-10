<?php

declare(strict_types=1);

namespace application\usecase\command\template;

use application\service\ClockInterface;
use application\usecase\exception\ResourceNotFoundException;
use application\usecase\support\IsoDateTime;
use domain\template\repository\TemplateRepositoryInterface;

final class DeactivateTemplateUseCase implements DeactivateTemplateUseCaseInterface
{
    public function __construct(
        private readonly TemplateRepositoryInterface $templateRepository,
        private readonly ClockInterface $clock
    ) {
    }

    public function execute(DeactivateTemplateCommand $command): DeactivateTemplateResult
    {
        $template = $this->templateRepository->getByIdForOwner($command->templateId, $command->actorId);
        if ($template === null) {
            throw new ResourceNotFoundException('template.not_found: ' . $command->templateId);
        }

        $template->deactivate($command->actorId, $this->clock->now());
        $this->templateRepository->save($template);

        return new DeactivateTemplateResult(
            templateId: $template->templateId,
            isActive: $template->isActive,
            updatedAt: IsoDateTime::format($template->updatedAt)
        );
    }
}
