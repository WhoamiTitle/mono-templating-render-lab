<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\openapi;

use infrastructure\presentation\http\attribute\OpenApi;
use infrastructure\presentation\http\attribute\Route;
use ReflectionClass;

final class OpenApiDocumentFactory
{
    /**
     * @param class-string[] $controllerClasses
     */
    public function __construct(
        private readonly array $controllerClasses
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function create(): array
    {
        $paths = [];

        foreach ($this->controllerClasses as $controllerClass) {
            $reflection = new ReflectionClass($controllerClass);
            $route = $this->attribute($reflection, Route::class);
            $operation = $this->attribute($reflection, OpenApi::class);
            if (!$route instanceof Route || !$operation instanceof OpenApi) {
                continue;
            }

            $paths[$route->path][strtolower($route->method)] = $this->operation($route, $operation);
        }

        ksort($paths);

        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'Templating Render Lab API',
                'version' => '0.1.0',
            ],
            'servers' => [
                ['url' => '/'],
            ],
            'paths' => $paths,
            'components' => [
                'securitySchemes' => [
                    'xActorId' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'x-actor-id',
                    ],
                    'sessionCookie' => [
                        'type' => 'apiKey',
                        'in' => 'cookie',
                        'name' => 'session_id',
                    ],
                ],
                'schemas' => $this->schemas(),
            ],
        ];
    }

    /**
     * @template T of object
     * @param ReflectionClass<object> $reflection
     * @param class-string<T> $attributeClass
     */
    private function attribute(ReflectionClass $reflection, string $attributeClass): ?object
    {
        $attributes = $reflection->getAttributes($attributeClass);
        if ($attributes === []) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    /**
     * @return array<string, mixed>
     */
    private function operation(Route $route, OpenApi $operation): array
    {
        $result = [
            'tags' => $operation->tags,
            'summary' => $operation->summary,
            'parameters' => $this->parameters($route->path, $operation->queryParameters),
            'responses' => $this->responses($operation),
        ];

        if ($operation->requestBody !== null) {
            $result['requestBody'] = [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => $this->schemaRef($operation->requestBody),
                    ],
                ],
            ];
        }

        if ($operation->security !== []) {
            $security = [];
            foreach ($operation->security as $scheme) {
                $security[$scheme] = [];
            }
            $result['security'] = [$security];
        }

        return $result;
    }

    /**
     * @param string[] $queryParameters
     * @return array<int, array<string, mixed>>
     */
    private function parameters(string $path, array $queryParameters): array
    {
        $parameters = [];
        if (preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $path, $matches) === 1) {
            foreach ($matches[1] as $name) {
                $parameters[] = [
                    'name' => $name,
                    'in' => 'path',
                    'required' => true,
                    'schema' => ['type' => 'string'],
                ];
            }
        }

        foreach ($queryParameters as $name) {
            $parameters[] = [
                'name' => $name,
                'in' => 'query',
                'required' => false,
                'schema' => ['type' => $name === 'limit' || $name === 'iterationsN' ? 'integer' : 'string'],
            ];
        }

        return $parameters;
    }

    /**
     * @return array<string, mixed>
     */
    private function responses(OpenApi $operation): array
    {
        $responses = [
            (string)$operation->responseStatus => [
                'description' => $operation->responseStatus === 204 ? 'No content' : 'Success',
            ],
        ];

        if ($operation->response !== null && $operation->responseStatus !== 204) {
            $responses[(string)$operation->responseStatus]['content'] = [
                'application/json' => [
                    'schema' => $this->schemaRef($operation->response),
                ],
            ];
        }

        foreach ([400, 401, 403, 404, 409, 422, 500] as $status) {
            $responses[(string)$status] = [
                'description' => 'Error',
                'content' => [
                    'application/json' => [
                        'schema' => $this->schemaRef('ErrorResponse'),
                    ],
                ],
            ];
        }

        return $responses;
    }

    /**
     * @return array<string, string>
     */
    private function schemaRef(string $name): array
    {
        return ['$ref' => '#/components/schemas/' . $name];
    }

    /**
     * @return array<string, mixed>
     */
    private function schemas(): array
    {
        return [
            'ErrorResponse' => $this->object([
                'error' => $this->object([
                    'message' => ['type' => 'string'],
                    'details' => ['type' => 'object', 'additionalProperties' => true],
                ], ['message', 'details'], true),
            ], ['error']),
            'IdResponse' => $this->object(['id' => ['type' => 'string']], ['id']),
            'User' => $this->object(['userId' => ['type' => 'string'], 'email' => ['type' => 'string', 'format' => 'email']], ['userId', 'email']),
            'Session' => $this->object(['sessionId' => ['type' => 'string'], 'userId' => ['type' => 'string'], 'expiresAt' => ['type' => 'string', 'format' => 'date-time']], ['sessionId', 'userId', 'expiresAt']),
            'RegisterUserRequest' => $this->object(['email' => ['type' => 'string', 'format' => 'email'], 'password' => ['type' => 'string']], ['email', 'password']),
            'LoginUserRequest' => $this->object(['email' => ['type' => 'string', 'format' => 'email'], 'password' => ['type' => 'string']], ['email', 'password']),
            'Template' => $this->object([
                'templateId' => ['type' => 'string'],
                'ownerId' => ['type' => 'string'],
                'name' => ['type' => 'string'],
                'engineType' => ['type' => 'string'],
                'templateBody' => ['type' => 'string'],
                'isActive' => ['type' => 'boolean'],
                'createdAt' => ['type' => 'string', 'format' => 'date-time'],
                'updatedAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
            ]),
            'TemplateList' => $this->listOf('Template'),
            'TemplateStats' => $this->object([
                'templateId' => ['type' => 'string'],
                'runsTotal' => ['type' => 'integer'],
                'runsSuccess' => ['type' => 'integer'],
                'runsFailure' => ['type' => 'integer'],
                'avgDurationMs' => ['type' => ['number', 'null']],
            ]),
            'RegisterTemplateRequest' => $this->object(['name' => ['type' => 'string'], 'engineType' => ['type' => 'string'], 'templateBody' => ['type' => 'string']], ['name', 'engineType', 'templateBody']),
            'UpdateTemplateBodyRequest' => $this->object(['templateBody' => ['type' => 'string']], ['templateBody']),
            'RenderRun' => $this->object([
                'runId' => ['type' => 'string'],
                'ownerId' => ['type' => 'string'],
                'templateId' => ['type' => 'string'],
                'engineType' => ['type' => 'string'],
                'context' => ['type' => 'object', 'additionalProperties' => true],
                'status' => ['type' => 'string'],
                'durationMs' => ['type' => ['integer', 'null']],
                'outputText' => ['type' => ['string', 'null']],
                'errorCode' => ['type' => ['string', 'null']],
                'errorMessage' => ['type' => ['string', 'null']],
                'startedAt' => ['type' => 'string', 'format' => 'date-time'],
                'finishedAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
            ]),
            'RenderRunList' => $this->listOf('RenderRun'),
            'StartRenderRunRequest' => $this->object(['templateId' => ['type' => 'string'], 'context' => ['type' => 'object', 'additionalProperties' => true]], ['templateId', 'context']),
            'CompleteRenderRunSuccessRequest' => $this->object(['durationMs' => ['type' => ['integer', 'null']], 'outputText' => ['type' => 'string']], ['outputText']),
            'CompleteRenderRunFailureRequest' => $this->object(['durationMs' => ['type' => ['integer', 'null']], 'errorCode' => ['type' => ['string', 'null']], 'errorMessage' => ['type' => ['string', 'null']]]),
            'BenchmarkRun' => $this->object([
                'benchmarkRunId' => ['type' => 'string'],
                'ownerId' => ['type' => 'string'],
                'templateId' => ['type' => 'string'],
                'engineType' => ['type' => 'string'],
                'context' => ['type' => 'object', 'additionalProperties' => true],
                'iterationsN' => ['type' => 'integer'],
                'status' => ['type' => 'string'],
                'samplesMs' => ['type' => 'array', 'items' => ['type' => 'number']],
                'avgMs' => ['type' => ['number', 'null']],
                'minMs' => ['type' => ['number', 'null']],
                'maxMs' => ['type' => ['number', 'null']],
                'p95Ms' => ['type' => ['number', 'null']],
                'outputBytes' => ['type' => ['integer', 'null']],
                'errorCode' => ['type' => ['string', 'null']],
                'errorMessage' => ['type' => ['string', 'null']],
                'startedAt' => ['type' => 'string', 'format' => 'date-time'],
                'finishedAt' => ['type' => ['string', 'null'], 'format' => 'date-time'],
            ]),
            'BenchmarkRunList' => $this->listOf('BenchmarkRun'),
            'StartBenchmarkRunRequest' => $this->object(['templateId' => ['type' => 'string'], 'context' => ['type' => 'object', 'additionalProperties' => true], 'iterationsN' => ['type' => 'integer']], ['templateId', 'context', 'iterationsN']),
            'CompleteBenchmarkRunSuccessRequest' => $this->object([
                'samplesMs' => ['type' => 'array', 'items' => ['type' => 'number']],
                'avgMs' => ['type' => 'number'],
                'minMs' => ['type' => 'number'],
                'maxMs' => ['type' => 'number'],
                'p95Ms' => ['type' => 'number'],
                'outputBytes' => ['type' => ['integer', 'null']],
            ], ['samplesMs', 'avgMs', 'minMs', 'maxMs', 'p95Ms']),
            'CompleteBenchmarkRunFailureRequest' => $this->object(['errorCode' => ['type' => ['string', 'null']], 'errorMessage' => ['type' => ['string', 'null']]]),
            'SandboxSlot' => $this->object(['engineId' => ['type' => 'string'], 'code' => ['type' => 'string']], ['engineId', 'code']),
            'SaveStateRequest' => $this->object(['slotA' => $this->schemaRef('SandboxSlot'), 'slotB' => $this->schemaRef('SandboxSlot'), 'json' => ['type' => 'string']], ['slotA', 'slotB', 'json']),
        ];
    }

    /**
     * @param array<string, mixed> $properties
     * @param string[] $required
     * @return array<string, mixed>
     */
    private function object(array $properties, array $required = [], bool $additionalProperties = false): array
    {
        return [
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
            'additionalProperties' => $additionalProperties,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function listOf(string $schema): array
    {
        return $this->object([
            'items' => [
                'type' => 'array',
                'items' => $this->schemaRef($schema),
            ],
        ], ['items']);
    }
}
