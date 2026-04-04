<?php

declare(strict_types=1);

namespace domain\template\model;

use DateTimeImmutable;
use domain\common\exception\ValidationException;
use domain\render_run\model\RenderRun;
use domain\template\exception\TemplateAccessDeniedException;
use domain\template\exception\TemplateInactiveException;
use domain\template\value_object\EngineType;

class Template
{
    public function __construct(
        public string $templateId,
        public string $ownerId,
        public string $name,
        public string $engineType,
        public string $templateBody,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
        public bool $isActive = true
    ) {
        $this->templateId = trim($this->templateId);
        $this->ownerId = trim($this->ownerId);
        $this->name = trim($this->name);
        $this->engineType = EngineType::from($this->engineType)->value();
        $this->templateBody = trim($this->templateBody);

        if ($this->templateId === '') {
            throw new ValidationException('template.id.empty', 4202);
        }

        if ($this->ownerId === '') {
            throw new ValidationException('template.owner_id.empty: ' . $this->templateId, 4206);
        }

        if ($this->name === '') {
            throw new ValidationException('template.name.empty: ' . $this->templateId, 4207);
        }

        if ($this->templateBody === '') {
            throw new ValidationException('template.body.empty: ' . $this->templateId, 4203);
        }

        if ($this->updatedAt < $this->createdAt) {
            throw new ValidationException('template.updated_at.invalid: ' . $this->templateId, 4204);
        }
    }

    public static function register(
        string $templateId,
        string $ownerId,
        string $name,
        string $engineType,
        string $templateBody,
        DateTimeImmutable $createdAt
    ): self {
        return new self(
            templateId: $templateId,
            ownerId: $ownerId,
            name: $name,
            engineType: EngineType::from($engineType)->value(),
            templateBody: $templateBody,
            createdAt: $createdAt,
            updatedAt: $createdAt,
            isActive: true
        );
    }

    public function rename(string $actorId, string $name, DateTimeImmutable $updatedAt): void
    {
        $this->assertOwner($actorId);

        $name = trim($name);
        if ($name === '') {
            throw new ValidationException('template.name.empty: ' . $this->templateId, 4207);
        }

        if ($updatedAt < $this->updatedAt) {
            throw new ValidationException('template.updated_at.invalid: ' . $this->templateId, 4204);
        }

        $this->name = $name;
        $this->updatedAt = $updatedAt;
    }

    public function updateBody(string $actorId, string $templateBody, DateTimeImmutable $updatedAt): void
    {
        $this->assertOwner($actorId);

        if (!$this->isActive) {
            throw new TemplateInactiveException($this->templateId);
        }

        $templateBody = trim($templateBody);
        if ($templateBody === '') {
            throw new ValidationException('template.body.empty: ' . $this->templateId, 4203);
        }

        if ($updatedAt < $this->updatedAt) {
            throw new ValidationException('template.updated_at.invalid: ' . $this->templateId, 4204);
        }

        $this->templateBody = $templateBody;
        $this->updatedAt = $updatedAt;
    }

    public function deactivate(string $actorId, DateTimeImmutable $updatedAt): void
    {
        $this->assertOwner($actorId);

        if ($updatedAt < $this->updatedAt) {
            throw new ValidationException('template.updated_at.invalid: ' . $this->templateId, 4204);
        }

        $this->isActive = false;
        $this->updatedAt = $updatedAt;
    }

    public function startRenderRun(
        string $actorId,
        string $runId,
        array $contextJson,
        DateTimeImmutable $startedAt
    ): RenderRun {
        $this->assertOwner($actorId);

        if (!$this->isActive) {
            throw new TemplateInactiveException($this->templateId);
        }

        return RenderRun::start(
            runId: $runId,
            templateId: $this->templateId,
            ownerId: $this->ownerId,
            engineType: $this->engineType,
            templateBodySnapshot: $this->templateBody,
            contextJson: $contextJson,
            startedAt: $startedAt
        );
    }

    private function assertOwner(string $actorId): void
    {
        $actorId = trim($actorId);
        if ($actorId === '') {
            throw new ValidationException('template.actor_id.empty: ' . $this->templateId, 4208);
        }

        if ($actorId !== $this->ownerId) {
            throw new TemplateAccessDeniedException(
                templateId: $this->templateId,
                ownerId: $this->ownerId,
                actorId: $actorId
            );
        }
    }
}

