<?php

declare(strict_types=1);

namespace application\usecase\query\state;

final readonly class GetStateQuery
{
    public function __construct(
        public string $stateId
    ) {
    }
}
