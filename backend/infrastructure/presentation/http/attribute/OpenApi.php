<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class OpenApi
{
    /**
     * @param string[] $tags
     * @param string[] $queryParameters
     * @param string[] $security
     */
    public function __construct(
        public string $summary,
        public array $tags,
        public ?string $requestBody = null,
        public ?string $response = null,
        public int $responseStatus = 200,
        public array $queryParameters = [],
        public array $security = ['sessionCookie']
    ) {
    }
}
