<?php

declare(strict_types=1);

namespace infrastructure\presentation\http;

use application\usecase\exception\ConflictException;
use application\usecase\exception\ResourceNotFoundException;
use domain\account\exception\AuthSessionExpiredException;
use domain\account\exception\AuthenticationFailedException;
use domain\common\exception\AccessDeniedException;
use domain\common\exception\DomainException;
use domain\common\exception\ValidationException;
use infrastructure\presentation\http\exception\ConflictHttpException;
use infrastructure\presentation\http\exception\ForbiddenHttpException;
use infrastructure\presentation\http\exception\HttpException;
use infrastructure\presentation\http\exception\NotFoundHttpException;
use infrastructure\presentation\http\exception\UnauthorizedHttpException;
use infrastructure\presentation\http\exception\UnprocessableEntityHttpException;

final class HttpExceptionResponder
{
    public function toResponse(\Throwable $exception): HttpResponse
    {
        $httpException = $this->normalize($exception);

        return JsonResponse::createError(
            statusCode: $httpException->statusCode(),
            payload: [
                'error' => [
                    'message' => $httpException->getMessage(),
                    'details' => $httpException->details(),
                ],
            ]
        );
    }

    private function normalize(\Throwable $exception): HttpException
    {
        if ($exception instanceof HttpException) {
            return $exception;
        }

        if ($exception instanceof ResourceNotFoundException) {
            return new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }

        if ($exception instanceof ConflictException) {
            return new ConflictHttpException($exception->getMessage(), previous: $exception);
        }

        if ($exception instanceof AuthenticationFailedException || $exception instanceof AuthSessionExpiredException) {
            return new UnauthorizedHttpException($exception->getMessage(), previous: $exception);
        }

        if ($exception instanceof AccessDeniedException) {
            return new ForbiddenHttpException($exception->getMessage(), previous: $exception);
        }

        if ($exception instanceof ValidationException || $exception instanceof DomainException) {
            return new UnprocessableEntityHttpException($exception->getMessage(), previous: $exception);
        }

        return new HttpException('internal_server_error', 500, previous: $exception);
    }
}
