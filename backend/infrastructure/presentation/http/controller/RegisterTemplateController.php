<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\controller;

use application\usecase\command\template\DeactivateTemplateCommand;
use application\usecase\command\template\DeactivateTemplateUseCaseInterface;
use application\usecase\command\template\RegisterTemplateCommand;
use application\usecase\command\template\RegisterTemplateUseCaseInterface;
use application\usecase\command\template\UpdateTemplateBodyCommand;
use application\usecase\command\template\UpdateTemplateBodyUseCaseInterface;
use infrastructure\presentation\http\HttpRequest;
use infrastructure\presentation\http\HttpResponse;
use infrastructure\presentation\http\JsonResponse;
use infrastructure\presentation\http\exception\BadRequestHttpException;

final class RegisterTemplateController extends AbstractJsonController
{
    public function __construct(
        private readonly RegisterTemplateUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $payload = $this->body($request);
        $engineType = $this->requiredStringByAliases($payload, ['engineType', 'engineId'], 'engineType');
        $templateBody = $this->requiredStringByAliases($payload, ['templateBody', 'code'], 'templateBody');
        $isPublic = $payload['isPublic'] ?? false;
        if (!is_bool($isPublic)) {
            throw new BadRequestHttpException('request.field.invalid_bool', ['field' => 'isPublic']);
        }

        $result = $this->useCase->execute(new RegisterTemplateCommand(
            actorId: $this->requireActorId($request),
            name: $this->requireString($payload, 'name'),
            engineType: $engineType,
            templateBody: $templateBody,
            isPublic: $isPublic
        ));

        return JsonResponse::created($result->toArray());
    }

    /**
     * @param array<string, mixed> $payload
     * @param string[] $aliases
     */
    private function requiredStringByAliases(array $payload, array $aliases, string $fieldName): string
    {
        foreach ($aliases as $alias) {
            $value = $payload[$alias] ?? null;
            if (!is_string($value)) {
                continue;
            }

            $trimmed = trim($value);
            if ($trimmed !== '') {
                return $trimmed;
            }
        }

        throw new BadRequestHttpException('request.field.required', ['field' => $fieldName]);
    }
}

final class UpdateTemplateBodyController extends AbstractJsonController
{
    public function __construct(
        private readonly UpdateTemplateBodyUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $payload = $this->body($request);
        $templateBody = $this->requiredStringByAliases($payload, ['templateBody', 'code'], 'templateBody');

        $result = $this->useCase->execute(new UpdateTemplateBodyCommand(
            actorId: $this->requireActorId($request),
            templateId: $this->requireRouteParam($request, 'templateId'),
            templateBody: $templateBody
        ));

        return JsonResponse::ok($result->toArray());
    }

    /**
     * @param array<string, mixed> $payload
     * @param string[] $aliases
     */
    private function requiredStringByAliases(array $payload, array $aliases, string $fieldName): string
    {
        foreach ($aliases as $alias) {
            $value = $payload[$alias] ?? null;
            if (!is_string($value)) {
                continue;
            }

            $trimmed = trim($value);
            if ($trimmed !== '') {
                return $trimmed;
            }
        }

        throw new BadRequestHttpException('request.field.required', ['field' => $fieldName]);
    }
}

final class DeactivateTemplateController extends AbstractJsonController
{
    public function __construct(
        private readonly DeactivateTemplateUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $result = $this->useCase->execute(new DeactivateTemplateCommand(
            actorId: $this->requireActorId($request),
            templateId: $this->requireRouteParam($request, 'templateId')
        ));

        return JsonResponse::ok($result->toArray());
    }
}
