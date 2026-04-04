<?php

declare(strict_types=1);

namespace domain\account\exception;

use domain\common\exception\DomainException;

class AuthSessionExpiredException extends DomainException
{
    public function __construct(string $sessionId)
    {
        parent::__construct('account.exception.session_expired: ' . $sessionId, 4412);
    }
}
