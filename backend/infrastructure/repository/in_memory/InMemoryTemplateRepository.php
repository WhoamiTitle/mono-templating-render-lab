<?php

declare(strict_types=1);

namespace infrastructure\repository\in_memory;

use domain\template\model\Template;
use domain\template\repository\TemplateRepositoryInterface;

final class InMemoryTemplateRepository implements TemplateRepositoryInterface
{
    /**
     * @var array<string, Template>
     */
    private array $templates = [];

    public function save(Template $template): void
    {
        $this->templates[$template->templateId] = clone $template;
    }

    public function getById(string $templateId): ?Template
    {
        $template = $this->templates[$templateId] ?? null;

        return $template !== null ? clone $template : null;
    }

    public function getByIdForOwner(string $templateId, string $ownerId): ?Template
    {
        $template = $this->templates[$templateId] ?? null;
        if ($template === null || $template->ownerId !== $ownerId) {
            return null;
        }

        return clone $template;
    }

    public function listByOwner(string $ownerId, array $filters = []): array
    {
        $filters['ownerId'] = $ownerId;

        return InMemoryRepositoryTools::filterByProperties($this->templates, $filters);
    }

    public function listPublic(array $filters = []): array
    {
        $filters['isPublic'] = true;
        $filters['isActive'] = true;

        return InMemoryRepositoryTools::filterByProperties($this->templates, $filters);
    }
}
