<?php

declare(strict_types=1);

namespace domain\account\exception;

use domain\common\exception\DomainException;

class AuthenticationFailedException extends DomainException
{
    public function __construct(string $email)
    {
        parent::__construct('account.exception.authentication_failed: ' . $email, 4411);
    }
}
