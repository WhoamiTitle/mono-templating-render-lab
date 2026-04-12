<?php

declare(strict_types=1);

namespace application\usecase\query\state;

interface GetStateUseCaseInterface
{
    public function execute(GetStateQuery $query): StateView;
}
