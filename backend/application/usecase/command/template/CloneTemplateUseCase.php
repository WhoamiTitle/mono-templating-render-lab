<?php

declare(strict_types=1);

namespace application\usecase\command\template;

use application\service\ClockInterface;
use application\service\IdGeneratorInterface;
use application\usecase\exception\ResourceNotFoundException;
use application\usecase\support\IsoDateTime;
use domain\template\repository\TemplateRepositoryInterface;

final class CloneTemplateUseCase implements CloneTemplateUseCaseInterface
{
    public function __construct(
        private readonly TemplateRepositoryInterface $templateRepository,
        private readonly IdGeneratorInterface $idGenerator,
        private readonly ClockInterface $clock
    ) {
    }

    public function execute(CloneTemplateCommand $command): CloneTemplateResult
    {
        $source = $this->templateRepository->getById($command->templateId);
        if ($source === null) {
            throw new ResourceNotFoundException('template.not_found: ' . $command->templateId);
        }

        if (!$source->isPublic && $source->ownerId !== $command->actorId) {
            throw new ResourceNotFoundException('template.not_found: ' . $command->templateId);
        }

        $clone = $source->cloneForOwner(
            newTemplateId: $this->idGenerator->generate(),
            newOwnerId: $command->actorId,
            createdAt: $this->clock->now()
        );

        $this->templateRepository->save($clone);

        return new CloneTemplateResult(
            templateId: $clone->templateId,
            ownerId: $clone->ownerId,
            name: $clone->name,
            engineType: $clone->engineType,
            templateBody: $clone->templateBody,
            isPublic: $clone->isPublic,
            isActive: $clone->isActive,
            createdAt: IsoDateTime::format($clone->createdAt),
            updatedAt: IsoDateTime::format($clone->updatedAt)
        );
    }
}
