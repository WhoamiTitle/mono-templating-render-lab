<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres\mapper;

use DateTimeImmutable;
use domain\render_run\model\RenderRun;
use infrastructure\repository\postgres\JsonValue;

final class RenderRunRowMapper
{
    /**
     * @param array<string, mixed> $row
     */
    public static function toModel(array $row): RenderRun
    {
        return new RenderRun(
            runId: (string)$row['run_id'],
            templateId: (string)$row['template_id'],
            ownerId: (string)$row['owner_id'],
            engineType: (string)$row['engine_type'],
            templateBodySnapshot: (string)$row['template_body_snapshot'],
            contextJson: JsonValue::decode((string)$row['context_json']),
            startedAt: new DateTimeImmutable((string)$row['started_at']),
            finishedAt: $row['finished_at'] !== null ? new DateTimeImmutable((string)$row['finished_at']) : null,
            status: (string)$row['status'],
            durationMs: $row['duration_ms'] !== null ? (int)$row['duration_ms'] : null,
            outputText: $row['output_text'] !== null ? (string)$row['output_text'] : null,
            errorCode: $row['error_code'] !== null ? (string)$row['error_code'] : null,
            errorMessage: $row['error_message'] !== null ? (string)$row['error_message'] : null
        );
    }
}
