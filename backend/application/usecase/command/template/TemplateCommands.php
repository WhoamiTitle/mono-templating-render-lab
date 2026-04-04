<?php

declare(strict_types=1);

namespace application\usecase\command\template;

final readonly class RegisterTemplateCommand
{
    public function __construct(
        public string $actorId,
        public string $name,
        public string $engineType,
        public string $templateBody
    ) {
    }
}

final readonly class UpdateTemplateBodyCommand
{
    public function __construct(
        public string $actorId,
        public string $templateId,
        public string $templateBody
    ) {
    }
}

final readonly class DeactivateTemplateCommand
{
    public function __construct(
        public string $actorId,
        public string $templateId
    ) {
    }
}
