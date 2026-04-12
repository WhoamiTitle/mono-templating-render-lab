<?php

declare(strict_types=1);

namespace application\usecase\command\state;

final readonly class SaveStateCommand
{
    /**
     * @param array<string, mixed> $state
     */
    public function __construct(
        public array $state,
        public ?string $ownerId = null
    ) {
    }
}
