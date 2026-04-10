<?php

declare(strict_types=1);

namespace application\service;

use DateTimeImmutable;

interface ClockInterface
{
    public function now(): DateTimeImmutable;
}
