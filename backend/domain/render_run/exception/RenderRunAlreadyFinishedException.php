<?php

declare(strict_types=1);

namespace domain\render_run\exception;

use domain\common\exception\DomainException;

class RenderRunAlreadyFinishedException extends DomainException
{
    public function __construct(string $runId)
    {
        parent::__construct('render_run.exception.already_finished: ' . $runId, 4301);
    }
}

