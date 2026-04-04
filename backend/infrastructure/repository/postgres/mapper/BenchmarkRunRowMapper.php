<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres\mapper;

use DateTimeImmutable;
use domain\benchmark_run\model\BenchmarkRun;
use infrastructure\repository\postgres\JsonValue;

final class BenchmarkRunRowMapper
{
    /**
     * @param array<string, mixed> $row
     */
    public static function toModel(array $row): BenchmarkRun
    {
        $samples = JsonValue::decode((string)$row['samples_ms']);

        return new BenchmarkRun(
            benchmarkRunId: (string)$row['benchmark_run_id'],
            ownerId: (string)$row['owner_id'],
            templateId: (string)$row['template_id'],
            engineType: (string)$row['engine_type'],
            contextJson: JsonValue::decode((string)$row['context_json']),
            iterationsN: (int)$row['iterations_n'],
            startedAt: new DateTimeImmutable((string)$row['started_at']),
            finishedAt: $row['finished_at'] !== null ? new DateTimeImmutable((string)$row['finished_at']) : null,
            status: (string)$row['status'],
            samplesMs: array_map(static fn (mixed $sample): int => (int)$sample, $samples),
            avgMs: $row['avg_ms'] !== null ? (float)$row['avg_ms'] : null,
            minMs: $row['min_ms'] !== null ? (int)$row['min_ms'] : null,
            maxMs: $row['max_ms'] !== null ? (int)$row['max_ms'] : null,
            p95Ms: $row['p95_ms'] !== null ? (int)$row['p95_ms'] : null,
            outputBytes: $row['output_bytes'] !== null ? (int)$row['output_bytes'] : null,
            errorCode: $row['error_code'] !== null ? (string)$row['error_code'] : null,
            errorMessage: $row['error_message'] !== null ? (string)$row['error_message'] : null
        );
    }
}
