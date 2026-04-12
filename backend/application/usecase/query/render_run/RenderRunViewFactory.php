<?php

declare(strict_types=1);

namespace application\usecase\query\render_run;

use application\usecase\support\IsoDateTime;
use domain\render_run\model\RenderRun;

final class RenderRunViewFactory
{
    public static function fromModel(RenderRun $renderRun): RenderRunView
    {
        return new RenderRunView(
            runId: $renderRun->runId,
            templateId: $renderRun->templateId,
            ownerId: $renderRun->ownerId,
            engineType: $renderRun->engineType,
            templateBodySnapshot: $renderRun->templateBodySnapshot,
            contextJson: $renderRun->contextJson,
            startedAt: IsoDateTime::format($renderRun->startedAt),
            finishedAt: $renderRun->finishedAt !== null ? IsoDateTime::format($renderRun->finishedAt) : null,
            status: $renderRun->status,
            durationMs: $renderRun->durationMs,
            outputText: $renderRun->outputText,
            errorCode: $renderRun->errorCode,
            errorMessage: $renderRun->errorMessage
        );
    }
}
