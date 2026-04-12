<?php

declare(strict_types=1);

namespace application\usecase\command\state;

use application\usecase\command\Contract\CommandResultInterface;

final readonly class SaveStateResult implements CommandResultInterface
{
    public function __construct(
        public string $id
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
