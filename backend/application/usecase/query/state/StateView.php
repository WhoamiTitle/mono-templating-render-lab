<?php

declare(strict_types=1);

namespace application\usecase\query\state;

final readonly class StateView
{
    /**
     * @param array<string, mixed> $state
     */
    public function __construct(
        public array $state
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->state;
    }
}
