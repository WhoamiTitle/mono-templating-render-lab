<?php

declare(strict_types=1);

namespace infrastructure\repository\in_memory;

use domain\render_run\model\RenderRun;
use domain\render_run\repository\RenderRunRepositoryInterface;

final class InMemoryRenderRunRepository implements RenderRunRepositoryInterface
{
    /**
     * @var array<string, RenderRun>
     */
    private array $renderRuns = [];

    public function save(RenderRun $renderRun): void
    {
        $this->renderRuns[$renderRun->runId] = clone $renderRun;
    }

    public function getById(string $runId): ?RenderRun
    {
        $renderRun = $this->renderRuns[$runId] ?? null;

        return $renderRun !== null ? clone $renderRun : null;
    }

    public function getByIdForOwner(string $runId, string $ownerId): ?RenderRun
    {
        $renderRun = $this->renderRuns[$runId] ?? null;
        if ($renderRun === null || $renderRun->ownerId !== $ownerId) {
            return null;
        }

        return clone $renderRun;
    }

    public function listByOwner(string $ownerId, array $filters = []): array
    {
        $filters['ownerId'] = $ownerId;

        return InMemoryRepositoryTools::filterByProperties($this->renderRuns, $filters);
    }
}
