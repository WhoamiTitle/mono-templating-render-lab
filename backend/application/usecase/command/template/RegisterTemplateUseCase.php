<?php

declare(strict_types=1);

namespace application\usecase\command\template;

use application\service\ClockInterface;
use application\service\IdGeneratorInterface;
use application\usecase\support\IsoDateTime;
use domain\template\model\Template;
use domain\template\repository\TemplateRepositoryInterface;

final class RegisterTemplateUseCase implements RegisterTemplateUseCaseInterface
{
    public function __construct(
        private readonly TemplateRepositoryInterface $templateRepository,
        private readonly IdGeneratorInterface $idGenerator,
        private readonly ClockInterface $clock
    ) {
    }

    public function execute(RegisterTemplateCommand $command): RegisterTemplateResult
    {
        $createdAt = $this->clock->now();
        $template = Template::register(
            templateId: $this->idGenerator->generate(),
            ownerId: $command->actorId,
            name: $command->name,
            engineType: $command->engineType,
            templateBody: $command->templateBody,
            createdAt: $createdAt
        );

        $this->templateRepository->save($template);

        return new RegisterTemplateResult(
            templateId: $template->templateId,
            ownerId: $template->ownerId,
            name: $template->name,
            engineType: $template->engineType,
            isActive: $template->isActive,
            createdAt: IsoDateTime::format($template->createdAt),
            updatedAt: IsoDateTime::format($template->updatedAt)
        );
    }
}
