<?php

declare(strict_types=1);

namespace domain\benchmark_run\exception;

use domain\common\exception\DomainException;

class BenchmarkRunAlreadyFinishedException extends DomainException
{
    public function __construct(string $benchmarkRunId)
    {
        parent::__construct('benchmark_run.exception.already_finished: ' . $benchmarkRunId, 4601);
    }
}

