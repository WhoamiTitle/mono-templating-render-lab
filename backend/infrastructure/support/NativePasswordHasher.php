<?php

declare(strict_types=1);

namespace infrastructure\support;

use application\service\PasswordHasherInterface;
use domain\common\exception\ValidationException;

final class NativePasswordHasher implements PasswordHasherInterface
{
    public function hash(string $plainText): string
    {
        $plainText = trim($plainText);
        if ($plainText === '') {
            throw new ValidationException('account.password.empty', 4428);
        }

        $hash = password_hash($plainText, PASSWORD_DEFAULT);
        if (!is_string($hash) || $hash === '') {
            throw new \RuntimeException('account.password.hash_failed');
        }

        return $hash;
    }

    public function verify(string $plainText, string $hash): bool
    {
        return password_verify($plainText, $hash);
    }
}
