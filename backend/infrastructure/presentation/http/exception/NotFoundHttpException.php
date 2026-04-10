<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\exception;

final class NotFoundHttpException extends HttpException
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(string $message, array $details = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, 404, $details, $previous);
    }
}
