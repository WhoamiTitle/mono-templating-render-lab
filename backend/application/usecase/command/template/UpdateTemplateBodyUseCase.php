<?php

declare(strict_types=1);

namespace application\usecase\command\template;

use application\service\ClockInterface;
use application\usecase\exception\ResourceNotFoundException;
use application\usecase\support\IsoDateTime;
use domain\template\repository\TemplateRepositoryInterface;

final class UpdateTemplateBodyUseCase implements UpdateTemplateBodyUseCaseInterface
{
    public function __construct(
        private readonly TemplateRepositoryInterface $templateRepository,
        private readonly ClockInterface $clock
    ) {
    }

    public function execute(UpdateTemplateBodyCommand $command): UpdateTemplateBodyResult
    {
        $template = $this->templateRepository->getByIdForOwner($command->templateId, $command->actorId);
        if ($template === null) {
            throw new ResourceNotFoundException('template.not_found: ' . $command->templateId);
        }

        $template->updateBody($command->actorId, $command->templateBody, $this->clock->now());
        $this->templateRepository->save($template);

        return new UpdateTemplateBodyResult(
            templateId: $template->templateId,
            updatedAt: IsoDateTime::format($template->updatedAt)
        );
    }
}
