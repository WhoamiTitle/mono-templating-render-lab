<?php

declare(strict_types=1);

namespace domain\account\value_object;

use domain\common\exception\ValidationException;

final class UserStatus
{
    public const ACTIVE = 'active';

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function from(string $status): self
    {
        $normalized = strtolower(trim($status));

        if (!in_array($normalized, self::values(), true)) {
            throw new ValidationException('account.user_status.invalid: ' . $status, 4403);
        }

        return new self($normalized);
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function values(): array
    {
        return [
            self::ACTIVE,
        ];
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
