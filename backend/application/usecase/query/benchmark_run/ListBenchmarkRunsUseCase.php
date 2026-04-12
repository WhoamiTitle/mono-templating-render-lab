<?php

declare(strict_types=1);

namespace application\usecase\query\benchmark_run;

use domain\benchmark_run\model\BenchmarkRun;
use domain\benchmark_run\repository\BenchmarkRunRepositoryInterface;

final class ListBenchmarkRunsUseCase implements ListBenchmarkRunsUseCaseInterface
{
    public function __construct(
        private readonly BenchmarkRunRepositoryInterface $benchmarkRunRepository
    ) {
    }

    public function execute(ListBenchmarkRunsQuery $query): array
    {
        $benchmarkRuns = $this->benchmarkRunRepository->listByOwner($query->actorId, $query->filters);

        return array_map(
            static fn (BenchmarkRun $benchmarkRun): BenchmarkRunView => BenchmarkRunViewFactory::fromModel($benchmarkRun),
            $benchmarkRuns
        );
    }
}
