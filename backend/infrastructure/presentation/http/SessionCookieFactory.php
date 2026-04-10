<?php

declare(strict_types=1);

namespace infrastructure\presentation\http;

use DateTimeImmutable;
use DateTimeInterface;

final class SessionCookieFactory
{
    public function issue(string $sessionId, DateTimeInterface $expiresAt): string
    {
        return sprintf(
            'session_id=%s; Path=/; Expires=%s; HttpOnly; SameSite=Lax',
            rawurlencode($sessionId),
            gmdate('D, d M Y H:i:s T', $expiresAt->getTimestamp())
        );
    }

    public function expire(): string
    {
        return $this->issue('', new DateTimeImmutable('@0'));
    }
}
