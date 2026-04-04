<?php

declare(strict_types=1);

namespace application\usecase\command\template;

use application\usecase\command\Contract\CommandResultInterface;

interface RegisterTemplateUseCaseInterface
{
    public function execute(RegisterTemplateCommand $command): RegisterTemplateResult;
}

interface UpdateTemplateBodyUseCaseInterface
{
    public function execute(UpdateTemplateBodyCommand $command): UpdateTemplateBodyResult;
}

interface DeactivateTemplateUseCaseInterface
{
    public function execute(DeactivateTemplateCommand $command): DeactivateTemplateResult;
}

final readonly class RegisterTemplateResult implements CommandResultInterface
{
    public function __construct(
        public string $templateId,
        public string $ownerId,
        public string $name,
        public string $engineType,
        public bool $isActive,
        public string $createdAt,
        public string $updatedAt
    ) {
    }

    public function toArray(): array
    {
        return [
            'templateId' => $this->templateId,
            'ownerId' => $this->ownerId,
            'name' => $this->name,
            'engineType' => $this->engineType,
            'isActive' => $this->isActive,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}

final readonly class UpdateTemplateBodyResult implements CommandResultInterface
{
    public function __construct(
        public string $templateId,
        public string $updatedAt
    ) {
    }

    public function toArray(): array
    {
        return [
            'templateId' => $this->templateId,
            'updatedAt' => $this->updatedAt,
        ];
    }
}

final readonly class DeactivateTemplateResult implements CommandResultInterface
{
    public function __construct(
        public string $templateId,
        public bool $isActive,
        public string $updatedAt
    ) {
    }

    public function toArray(): array
    {
        return [
            'templateId' => $this->templateId,
            'isActive' => $this->isActive,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
