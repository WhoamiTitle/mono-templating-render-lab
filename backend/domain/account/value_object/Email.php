<?php

declare(strict_types=1);

namespace domain\account\value_object;

use domain\common\exception\ValidationException;

final class Email
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function from(string $email): self
    {
        $normalized = mb_strtolower(trim($email));

        if ($normalized === '') {
            throw new ValidationException('account.email.empty', 4401);
        }

        if (filter_var($normalized, FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidationException('account.email.invalid: ' . $email, 4402);
        }

        return new self($normalized);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

