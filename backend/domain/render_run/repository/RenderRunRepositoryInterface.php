<?php

declare(strict_types=1);

namespace domain\render_run\repository;

use domain\render_run\model\RenderRun;

interface RenderRunRepositoryInterface
{
    public function save(RenderRun $renderRun): void;

    public function getById(string $runId): ?RenderRun;

    public function getByIdForOwner(string $runId, string $ownerId): ?RenderRun;

    /**
     * @return RenderRun[]
     */
    public function listByOwner(string $ownerId, array $filters = []): array;
}
