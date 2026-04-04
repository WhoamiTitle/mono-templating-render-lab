<?php

declare(strict_types=1);

namespace infrastructure\repository\postgres\migration;

final readonly class MigrationConfig
{
    public function __construct(
        public string $directory
    ) {
    }
}
