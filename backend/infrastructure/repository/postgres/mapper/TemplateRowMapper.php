<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres\mapper;

use DateTimeImmutable;
use domain\template\model\Template;

final class TemplateRowMapper
{
    /**
     * @param array<string, mixed> $row
     */
    public static function toModel(array $row): Template
    {
        return new Template(
            templateId: (string)$row['template_id'],
            ownerId: (string)$row['owner_id'],
            name: (string)$row['name'],
            engineType: (string)$row['engine_type'],
            templateBody: (string)$row['template_body'],
            createdAt: new DateTimeImmutable((string)$row['created_at']),
            updatedAt: new DateTimeImmutable((string)$row['updated_at']),
            isPublic: (bool)($row['is_public'] ?? false),
            isActive: (bool)$row['is_active']
        );
    }
}
