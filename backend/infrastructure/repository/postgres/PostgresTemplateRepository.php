<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres;

use domain\template\model\Template;
use domain\template\repository\TemplateRepositoryInterface;
use infrastructure\repository\postgres\mapper\TemplateRowMapper;
use PDO;

final class PostgresTemplateRepository extends PostgresRepository implements TemplateRepositoryInterface
{
    private const FILTER_COLUMNS = [
        'templateId' => 'template_id',
        'ownerId' => 'owner_id',
        'engineType' => 'engine_type',
        'isActive' => 'is_active',
        'name' => 'name',
    ];

    public function __construct(PDO $connection)
    {
        parent::__construct($connection);
    }

    public function save(Template $template): void
    {
        $this->execute(
            <<<SQL
            INSERT INTO templates (
                template_id,
                owner_id,
                name,
                engine_type,
                template_body,
                created_at,
                updated_at,
                is_active
            ) VALUES (
                :template_id,
                :owner_id,
                :name,
                :engine_type,
                :template_body,
                :created_at,
                :updated_at,
                :is_active
            )
            ON CONFLICT (template_id) DO UPDATE SET
                owner_id = EXCLUDED.owner_id,
                name = EXCLUDED.name,
                engine_type = EXCLUDED.engine_type,
                template_body = EXCLUDED.template_body,
                created_at = EXCLUDED.created_at,
                updated_at = EXCLUDED.updated_at,
                is_active = EXCLUDED.is_active
            SQL,
            [
                'template_id' => $template->templateId,
                'owner_id' => $template->ownerId,
                'name' => $template->name,
                'engine_type' => $template->engineType,
                'template_body' => $template->templateBody,
                'created_at' => $template->createdAt,
                'updated_at' => $template->updatedAt,
                'is_active' => $template->isActive,
            ]
        );
    }

    public function getById(string $templateId): ?Template
    {
        $row = $this->fetchOne(
            'SELECT * FROM templates WHERE template_id = :template_id',
            ['template_id' => $templateId]
        );

        return $row !== null ? TemplateRowMapper::toModel($row) : null;
    }

    public function getByIdForOwner(string $templateId, string $ownerId): ?Template
    {
        $row = $this->fetchOne(
            'SELECT * FROM templates WHERE template_id = :template_id AND owner_id = :owner_id',
            [
                'template_id' => $templateId,
                'owner_id' => $ownerId,
            ]
        );

        return $row !== null ? TemplateRowMapper::toModel($row) : null;
    }

    public function listByOwner(string $ownerId, array $filters = []): array
    {
        unset($filters['ownerId']);
        $builtFilter = PostgresFilterBuilder::build($filters, self::FILTER_COLUMNS);

        $rows = $this->fetchAll(
            'SELECT * FROM templates WHERE owner_id = :owner_id'
            . $builtFilter['sql']
            . ' ORDER BY updated_at DESC, template_id ASC',
            [
                'owner_id' => $ownerId,
                ...$builtFilter['params'],
            ]
        );

        return array_map(static fn (array $row): Template => TemplateRowMapper::toModel($row), $rows);
    }
}
