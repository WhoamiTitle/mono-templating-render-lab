<?php

declare(strict_types=1);

namespace infrastructure\repository\in_memory;

use domain\benchmark_run\model\BenchmarkRun;
use domain\benchmark_run\repository\BenchmarkRunRepositoryInterface;

final class InMemoryBenchmarkRunRepository implements BenchmarkRunRepositoryInterface
{
    /**
     * @var array<string, BenchmarkRun>
     */
    private array $benchmarkRuns = [];

    public function save(BenchmarkRun $benchmarkRun): void
    {
        $this->benchmarkRuns[$benchmarkRun->benchmarkRunId] = clone $benchmarkRun;
    }

    public function getById(string $benchmarkRunId): ?BenchmarkRun
    {
        $benchmarkRun = $this->benchmarkRuns[$benchmarkRunId] ?? null;

        return $benchmarkRun !== null ? clone $benchmarkRun : null;
    }

    public function getByIdForOwner(string $benchmarkRunId, string $ownerId): ?BenchmarkRun
    {
        $benchmarkRun = $this->benchmarkRuns[$benchmarkRunId] ?? null;
        if ($benchmarkRun === null || $benchmarkRun->ownerId !== $ownerId) {
            return null;
        }

        return clone $benchmarkRun;
    }

    public function listByOwner(string $ownerId, array $filters = []): array
    {
        $filters['ownerId'] = $ownerId;

        return InMemoryRepositoryTools::filterByProperties($this->benchmarkRuns, $filters);
    }
}
