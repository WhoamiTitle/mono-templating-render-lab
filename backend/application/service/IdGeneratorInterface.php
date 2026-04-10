<?php

declare(strict_types=1);

namespace application\service;

interface IdGeneratorInterface
{
    public function generate(): string;
}
