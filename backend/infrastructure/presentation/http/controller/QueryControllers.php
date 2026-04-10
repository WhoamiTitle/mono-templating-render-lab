<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\controller;

use application\usecase\query\benchmark_run\GetBenchmarkRunQuery;
use application\usecase\query\benchmark_run\GetBenchmarkRunUseCaseInterface;
use application\usecase\query\benchmark_run\ListBenchmarkRunsQuery;
use application\usecase\query\benchmark_run\ListBenchmarkRunsUseCaseInterface;
use application\usecase\query\render_run\GetRecentFailuresQuery;
use application\usecase\query\render_run\GetRecentFailuresUseCaseInterface;
use application\usecase\query\render_run\GetRenderRunQuery;
use application\usecase\query\render_run\GetRenderRunUseCaseInterface;
use application\usecase\query\render_run\ListRenderRunsQuery;
use application\usecase\query\render_run\ListRenderRunsUseCaseInterface;
use application\usecase\query\template\GetTemplateQuery;
use application\usecase\query\template\GetTemplateStatsQuery;
use application\usecase\query\template\GetTemplateStatsUseCaseInterface;
use application\usecase\query\template\GetTemplateUseCaseInterface;
use application\usecase\query\template\ListTemplatesQuery;
use application\usecase\query\template\ListTemplatesUseCaseInterface;
use infrastructure\presentation\http\HttpRequest;
use infrastructure\presentation\http\HttpResponse;
use infrastructure\presentation\http\JsonResponse;

final class GetTemplateController extends AbstractJsonController
{
    public function __construct(
        private readonly GetTemplateUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $result = $this->useCase->execute(new GetTemplateQuery(
            actorId: $this->requireActorId($request),
            templateId: $this->requireRouteParam($request, 'templateId')
        ));

        return JsonResponse::ok($result->toArray());
    }
}

final class ListTemplatesController extends AbstractJsonController
{
    public function __construct(
        private readonly ListTemplatesUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $results = $this->useCase->execute(new ListTemplatesQuery(
            actorId: $this->requireActorId($request),
            filters: $this->filters($request, ['engineType', 'name', 'isActive'])
        ));

        return JsonResponse::ok([
            'items' => array_map(static fn ($view): array => $view->toArray(), $results),
        ]);
    }
}

final class GetTemplateStatsController extends AbstractJsonController
{
    public function __construct(
        private readonly GetTemplateStatsUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $result = $this->useCase->execute(new GetTemplateStatsQuery(
            actorId: $this->requireActorId($request),
            templateId: $this->requireRouteParam($request, 'templateId')
        ));

        return JsonResponse::ok($result->toArray());
    }
}

final class GetRenderRunController extends AbstractJsonController
{
    public function __construct(
        private readonly GetRenderRunUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $result = $this->useCase->execute(new GetRenderRunQuery(
            actorId: $this->requireActorId($request),
            runId: $this->requireRouteParam($request, 'runId')
        ));

        return JsonResponse::ok($result->toArray());
    }
}

final class ListRenderRunsController extends AbstractJsonController
{
    public function __construct(
        private readonly ListRenderRunsUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $results = $this->useCase->execute(new ListRenderRunsQuery(
            actorId: $this->requireActorId($request),
            filters: $this->filters($request, ['templateId', 'engineType', 'status'])
        ));

        return JsonResponse::ok([
            'items' => array_map(static fn ($view): array => $view->toArray(), $results),
        ]);
    }
}

final class GetRecentFailuresController extends AbstractJsonController
{
    public function __construct(
        private readonly GetRecentFailuresUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $limit = isset($request->queryParams['limit']) ? (int)$request->queryParams['limit'] : 10;
        $results = $this->useCase->execute(new GetRecentFailuresQuery(
            actorId: $this->requireActorId($request),
            limit: $limit > 0 ? $limit : 10
        ));

        return JsonResponse::ok([
            'items' => array_map(static fn ($view): array => $view->toArray(), $results),
        ]);
    }
}

final class GetBenchmarkRunController extends AbstractJsonController
{
    public function __construct(
        private readonly GetBenchmarkRunUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $result = $this->useCase->execute(new GetBenchmarkRunQuery(
            actorId: $this->requireActorId($request),
            benchmarkRunId: $this->requireRouteParam($request, 'benchmarkRunId')
        ));

        return JsonResponse::ok($result->toArray());
    }
}

final class ListBenchmarkRunsController extends AbstractJsonController
{
    public function __construct(
        private readonly ListBenchmarkRunsUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $results = $this->useCase->execute(new ListBenchmarkRunsQuery(
            actorId: $this->requireActorId($request),
            filters: $this->filters($request, ['templateId', 'engineType', 'status', 'iterationsN'])
        ));

        return JsonResponse::ok([
            'items' => array_map(static fn ($view): array => $view->toArray(), $results),
        ]);
    }
}
