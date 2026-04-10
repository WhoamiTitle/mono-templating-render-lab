<?php

declare(strict_types=1);

namespace infrastructure\support;

use application\service\ClockInterface;
use DateTimeImmutable;

final class SystemClock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
