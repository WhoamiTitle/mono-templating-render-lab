<?php

declare(strict_types=1);

namespace application\usecase\command\state;

interface SaveStateUseCaseInterface
{
    public function execute(SaveStateCommand $command): SaveStateResult;
}
