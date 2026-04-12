<?php

declare(strict_types=1);

namespace application\usecase\query\benchmark_run;

use application\usecase\exception\ResourceNotFoundException;
use domain\benchmark_run\repository\BenchmarkRunRepositoryInterface;

final class GetBenchmarkRunUseCase implements GetBenchmarkRunUseCaseInterface
{
    public function __construct(
        private readonly BenchmarkRunRepositoryInterface $benchmarkRunRepository
    ) {
    }

    public function execute(GetBenchmarkRunQuery $query): BenchmarkRunView
    {
        $benchmarkRun = $this->benchmarkRunRepository->getByIdForOwner($query->benchmarkRunId, $query->actorId);
        if ($benchmarkRun === null) {
            throw new ResourceNotFoundException('benchmark_run.not_found: ' . $query->benchmarkRunId);
        }

        return BenchmarkRunViewFactory::fromModel($benchmarkRun);
    }
}
