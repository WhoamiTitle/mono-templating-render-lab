<?php

declare(strict_types=1);

namespace infrastructure\repository\in_memory;

use domain\account\model\PasswordResetToken;
use domain\account\repository\PasswordResetTokenRepositoryInterface;

final class InMemoryPasswordResetTokenRepository implements PasswordResetTokenRepositoryInterface
{
    /**
     * @var array<string, PasswordResetToken>
     */
    private array $tokensById = [];

    /**
     * @var array<string, string>
     */
    private array $tokenIdsByHash = [];

    public function save(PasswordResetToken $token): void
    {
        $storedToken = clone $token;

        $previousToken = $this->tokensById[$storedToken->tokenId] ?? null;
        if ($previousToken !== null && $previousToken->tokenHash !== $storedToken->tokenHash) {
            unset($this->tokenIdsByHash[$previousToken->tokenHash]);
        }

        $this->tokensById[$storedToken->tokenId] = $storedToken;
        $this->tokenIdsByHash[$storedToken->tokenHash] = $storedToken->tokenId;
    }

    public function getById(string $tokenId): ?PasswordResetToken
    {
        $token = $this->tokensById[$tokenId] ?? null;

        return $token !== null ? clone $token : null;
    }

    public function getByTokenHash(string $tokenHash): ?PasswordResetToken
    {
        $tokenId = $this->tokenIdsByHash[$tokenHash] ?? null;
        if ($tokenId === null) {
            return null;
        }

        $token = $this->tokensById[$tokenId] ?? null;

        return $token !== null ? clone $token : null;
    }
}
