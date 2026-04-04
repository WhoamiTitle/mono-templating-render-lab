<?php

declare(strict_types=1);

namespace domain\account\exception;

use domain\common\exception\DomainException;

class PasswordResetTokenExpiredException extends DomainException
{
    public function __construct(string $tokenId)
    {
        parent::__construct('account.exception.password_reset_token_expired: ' . $tokenId, 4421);
    }
}

