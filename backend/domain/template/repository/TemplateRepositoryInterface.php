<?php

declare(strict_types=1);

namespace domain\template\repository;

use domain\template\model\Template;

interface TemplateRepositoryInterface
{
    public function save(Template $template): void;

    public function getById(string $templateId): ?Template;

    public function getByIdForOwner(string $templateId, string $ownerId): ?Template;

    /**
     * @return Template[]
     */
    public function listByOwner(string $ownerId, array $filters = []): array;

    /**
     * @return Template[]
     */
    public function listPublic(array $filters = []): array;
}
