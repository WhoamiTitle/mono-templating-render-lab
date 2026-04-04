<?php

declare(strict_types=1);

namespace application\usecase\command\Contract;

interface CommandResultInterface
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
