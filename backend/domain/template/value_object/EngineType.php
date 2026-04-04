<?php

declare(strict_types=1);

namespace domain\template\value_object;

use domain\common\exception\ValidationException;

final class EngineType
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function from(string $engineType): self
    {
        $normalized = strtolower(trim($engineType));

        if ($normalized === '') {
            throw new ValidationException('template.engine_type.invalid: ' . $engineType, 4001);
        }

        // Domain keeps engine type open-ended.
        // Allowed engines are managed by application/infra (DB/config), not hardcoded here.
        if (!preg_match('/^[a-z][a-z0-9_-]{1,31}$/', $normalized)) {
            throw new ValidationException('template.engine_type.invalid: ' . $engineType, 4001);
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
