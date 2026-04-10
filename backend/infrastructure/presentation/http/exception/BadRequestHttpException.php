<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\exception;

final class BadRequestHttpException extends HttpException
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(string $message, array $details = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, 400, $details, $previous);
    }
}
