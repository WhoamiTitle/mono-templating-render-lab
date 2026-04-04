<?php

declare(strict_types=1);

namespace domain\account\exception;

use domain\common\exception\DomainException;

class PasswordResetTokenAlreadyUsedException extends DomainException
{
    public function __construct(string $tokenId)
    {
        parent::__construct('account.exception.password_reset_token_used: ' . $tokenId, 4422);
    }
}

