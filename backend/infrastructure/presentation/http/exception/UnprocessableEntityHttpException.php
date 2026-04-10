<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\exception;

final class UnprocessableEntityHttpException extends HttpException
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(string $message, array $details = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, 422, $details, $previous);
    }
}
