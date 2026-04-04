<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres;

use domain\render_run\model\RenderRun;
use domain\render_run\repository\RenderRunRepositoryInterface;
use infrastructure\repository\postgres\mapper\RenderRunRowMapper;
use PDO;

final class PostgresRenderRunRepository extends PostgresRepository implements RenderRunRepositoryInterface
{
    private const FILTER_COLUMNS = [
        'runId' => 'run_id',
        'templateId' => 'template_id',
        'ownerId' => 'owner_id',
        'engineType' => 'engine_type',
        'status' => 'status',
    ];

    public function __construct(PDO $connection)
    {
        parent::__construct($connection);
    }

    public function save(RenderRun $renderRun): void
    {
        $this->execute(
            <<<SQL
            INSERT INTO render_runs (
                run_id,
                template_id,
                owner_id,
                engine_type,
                template_body_snapshot,
                context_json,
                started_at,
                finished_at,
                status,
                duration_ms,
                output_text,
                error_code,
                error_message
            ) VALUES (
                :run_id,
                :template_id,
                :owner_id,
                :engine_type,
                :template_body_snapshot,
                :context_json,
                :started_at,
                :finished_at,
                :status,
                :duration_ms,
                :output_text,
                :error_code,
                :error_message
            )
            ON CONFLICT (run_id) DO UPDATE SET
                template_id = EXCLUDED.template_id,
                owner_id = EXCLUDED.owner_id,
                engine_type = EXCLUDED.engine_type,
                template_body_snapshot = EXCLUDED.template_body_snapshot,
                context_json = EXCLUDED.context_json,
                started_at = EXCLUDED.started_at,
                finished_at = EXCLUDED.finished_at,
                status = EXCLUDED.status,
                duration_ms = EXCLUDED.duration_ms,
                output_text = EXCLUDED.output_text,
                error_code = EXCLUDED.error_code,
                error_message = EXCLUDED.error_message
            SQL,
            [
                'run_id' => $renderRun->runId,
                'template_id' => $renderRun->templateId,
                'owner_id' => $renderRun->ownerId,
                'engine_type' => $renderRun->engineType,
                'template_body_snapshot' => $renderRun->templateBodySnapshot,
                'context_json' => $renderRun->contextJson,
                'started_at' => $renderRun->startedAt,
                'finished_at' => $renderRun->finishedAt,
                'status' => $renderRun->status,
                'duration_ms' => $renderRun->durationMs,
                'output_text' => $renderRun->outputText,
                'error_code' => $renderRun->errorCode,
                'error_message' => $renderRun->errorMessage,
            ]
        );
    }

    public function getById(string $runId): ?RenderRun
    {
        $row = $this->fetchOne(
            'SELECT * FROM render_runs WHERE run_id = :run_id',
            ['run_id' => $runId]
        );

        return $row !== null ? RenderRunRowMapper::toModel($row) : null;
    }

    public function getByIdForOwner(string $runId, string $ownerId): ?RenderRun
    {
        $row = $this->fetchOne(
            'SELECT * FROM render_runs WHERE run_id = :run_id AND owner_id = :owner_id',
            [
                'run_id' => $runId,
                'owner_id' => $ownerId,
            ]
        );

        return $row !== null ? RenderRunRowMapper::toModel($row) : null;
    }

    public function listByOwner(string $ownerId, array $filters = []): array
    {
        unset($filters['ownerId']);
        $builtFilter = PostgresFilterBuilder::build($filters, self::FILTER_COLUMNS);

        $rows = $this->fetchAll(
            'SELECT * FROM render_runs WHERE owner_id = :owner_id'
            . $builtFilter['sql']
            . ' ORDER BY started_at DESC, run_id ASC',
            [
                'owner_id' => $ownerId,
                ...$builtFilter['params'],
            ]
        );

        return array_map(static fn (array $row): RenderRun => RenderRunRowMapper::toModel($row), $rows);
    }
}
