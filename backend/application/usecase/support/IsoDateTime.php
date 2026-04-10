<?php

declare(strict_types=1);

namespace application\usecase\support;

use DateTimeInterface;

final class IsoDateTime
{
    public static function format(DateTimeInterface $value): string
    {
        return $value->format(DateTimeInterface::ATOM);
    }
}
