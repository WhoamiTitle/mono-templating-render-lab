<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres;

use domain\benchmark_run\model\BenchmarkRun;
use domain\benchmark_run\repository\BenchmarkRunRepositoryInterface;
use infrastructure\repository\postgres\mapper\BenchmarkRunRowMapper;
use PDO;

final class PostgresBenchmarkRunRepository extends PostgresRepository implements BenchmarkRunRepositoryInterface
{
    private const FILTER_COLUMNS = [
        'benchmarkRunId' => 'benchmark_run_id',
        'templateId' => 'template_id',
        'ownerId' => 'owner_id',
        'engineType' => 'engine_type',
        'status' => 'status',
        'iterationsN' => 'iterations_n',
    ];

    public function __construct(PDO $connection)
    {
        parent::__construct($connection);
    }

    public function save(BenchmarkRun $benchmarkRun): void
    {
        $this->execute(
            <<<SQL
            INSERT INTO benchmark_runs (
                benchmark_run_id,
                owner_id,
                template_id,
                engine_type,
                context_json,
                iterations_n,
                started_at,
                finished_at,
                status,
                samples_ms,
                avg_ms,
                min_ms,
                max_ms,
                p95_ms,
                output_bytes,
                error_code,
                error_message
            ) VALUES (
                :benchmark_run_id,
                :owner_id,
                :template_id,
                :engine_type,
                :context_json,
                :iterations_n,
                :started_at,
                :finished_at,
                :status,
                :samples_ms,
                :avg_ms,
                :min_ms,
                :max_ms,
                :p95_ms,
                :output_bytes,
                :error_code,
                :error_message
            )
            ON CONFLICT (benchmark_run_id) DO UPDATE SET
                owner_id = EXCLUDED.owner_id,
                template_id = EXCLUDED.template_id,
                engine_type = EXCLUDED.engine_type,
                context_json = EXCLUDED.context_json,
                iterations_n = EXCLUDED.iterations_n,
                started_at = EXCLUDED.started_at,
                finished_at = EXCLUDED.finished_at,
                status = EXCLUDED.status,
                samples_ms = EXCLUDED.samples_ms,
                avg_ms = EXCLUDED.avg_ms,
                min_ms = EXCLUDED.min_ms,
                max_ms = EXCLUDED.max_ms,
                p95_ms = EXCLUDED.p95_ms,
                output_bytes = EXCLUDED.output_bytes,
                error_code = EXCLUDED.error_code,
                error_message = EXCLUDED.error_message
            SQL,
            [
                'benchmark_run_id' => $benchmarkRun->benchmarkRunId,
                'owner_id' => $benchmarkRun->ownerId,
                'template_id' => $benchmarkRun->templateId,
                'engine_type' => $benchmarkRun->engineType,
                'context_json' => $benchmarkRun->contextJson,
                'iterations_n' => $benchmarkRun->iterationsN,
                'started_at' => $benchmarkRun->startedAt,
                'finished_at' => $benchmarkRun->finishedAt,
                'status' => $benchmarkRun->status,
                'samples_ms' => $benchmarkRun->samplesMs,
                'avg_ms' => $benchmarkRun->avgMs,
                'min_ms' => $benchmarkRun->minMs,
                'max_ms' => $benchmarkRun->maxMs,
                'p95_ms' => $benchmarkRun->p95Ms,
                'output_bytes' => $benchmarkRun->outputBytes,
                'error_code' => $benchmarkRun->errorCode,
                'error_message' => $benchmarkRun->errorMessage,
            ]
        );
    }

    public function getById(string $benchmarkRunId): ?BenchmarkRun
    {
        $row = $this->fetchOne(
            'SELECT * FROM benchmark_runs WHERE benchmark_run_id = :benchmark_run_id',
            ['benchmark_run_id' => $benchmarkRunId]
        );

        return $row !== null ? BenchmarkRunRowMapper::toModel($row) : null;
    }

    public function getByIdForOwner(string $benchmarkRunId, string $ownerId): ?BenchmarkRun
    {
        $row = $this->fetchOne(
            'SELECT * FROM benchmark_runs WHERE benchmark_run_id = :benchmark_run_id AND owner_id = :owner_id',
            [
                'benchmark_run_id' => $benchmarkRunId,
                'owner_id' => $ownerId,
            ]
        );

        return $row !== null ? BenchmarkRunRowMapper::toModel($row) : null;
    }

    public function listByOwner(string $ownerId, array $filters = []): array
    {
        unset($filters['ownerId']);
        $builtFilter = PostgresFilterBuilder::build($filters, self::FILTER_COLUMNS);

        $rows = $this->fetchAll(
            'SELECT * FROM benchmark_runs WHERE owner_id = :owner_id'
            . $builtFilter['sql']
            . ' ORDER BY started_at DESC, benchmark_run_id ASC',
            [
                'owner_id' => $ownerId,
                ...$builtFilter['params'],
            ]
        );

        return array_map(static fn (array $row): BenchmarkRun => BenchmarkRunRowMapper::toModel($row), $rows);
    }
}
