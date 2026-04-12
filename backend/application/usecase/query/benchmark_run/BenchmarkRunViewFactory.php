<?php

declare(strict_types=1);

namespace application\usecase\query\benchmark_run;

use application\usecase\support\IsoDateTime;
use domain\benchmark_run\model\BenchmarkRun;

final class BenchmarkRunViewFactory
{
    public static function fromModel(BenchmarkRun $benchmarkRun): BenchmarkRunView
    {
        return new BenchmarkRunView(
            benchmarkRunId: $benchmarkRun->benchmarkRunId,
            ownerId: $benchmarkRun->ownerId,
            templateId: $benchmarkRun->templateId,
            engineType: $benchmarkRun->engineType,
            contextJson: $benchmarkRun->contextJson,
            iterationsN: $benchmarkRun->iterationsN,
            startedAt: IsoDateTime::format($benchmarkRun->startedAt),
            finishedAt: $benchmarkRun->finishedAt !== null ? IsoDateTime::format($benchmarkRun->finishedAt) : null,
            status: $benchmarkRun->status,
            samplesMs: $benchmarkRun->samplesMs,
            avgMs: $benchmarkRun->avgMs,
            minMs: $benchmarkRun->minMs,
            maxMs: $benchmarkRun->maxMs,
            p95Ms: $benchmarkRun->p95Ms,
            outputBytes: $benchmarkRun->outputBytes,
            errorCode: $benchmarkRun->errorCode,
            errorMessage: $benchmarkRun->errorMessage
        );
    }
}
