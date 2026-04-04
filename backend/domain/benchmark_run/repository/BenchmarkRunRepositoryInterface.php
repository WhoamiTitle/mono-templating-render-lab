<?php

declare(strict_types=1);

namespace domain\benchmark_run\repository;

use domain\benchmark_run\model\BenchmarkRun;

interface BenchmarkRunRepositoryInterface
{
    public function save(BenchmarkRun $benchmarkRun): void;

    public function getById(string $benchmarkRunId): ?BenchmarkRun;

    public function getByIdForOwner(string $benchmarkRunId, string $ownerId): ?BenchmarkRun;

    /**
     * @return BenchmarkRun[]
     */
    public function listByOwner(string $ownerId, array $filters = []): array;
}

