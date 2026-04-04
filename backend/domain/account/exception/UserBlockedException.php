<?php

declare(strict_types=1);

namespace domain\account\exception;

use domain\common\exception\DomainException;

class UserBlockedException extends DomainException
{
    public function __construct(string $userId)
    {
        parent::__construct('account.exception.user_blocked: ' . $userId, 4410);
    }
}
