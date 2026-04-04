<?php

declare(strict_types=1);

namespace domain\benchmark_run\value_object;

use domain\common\exception\ValidationException;

final class BenchmarkStatus
{
    public const IN_PROGRESS = 'in_progress';
    public const SUCCESS = 'success';
    public const FAILED = 'failed';

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function from(string $status): self
    {
        $normalized = strtolower(trim($status));

        if (!in_array($normalized, self::values(), true)) {
            throw new ValidationException('benchmark_run.status.invalid: ' . $status, 4501);
        }

        return new self($normalized);
    }

    public static function inProgress(): self
    {
        return new self(self::IN_PROGRESS);
    }

    public static function values(): array
    {
        return [
            self::IN_PROGRESS,
            self::SUCCESS,
            self::FAILED,
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

