<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\controller;

use application\usecase\query\template\ListPublicTemplatesQuery;
use application\usecase\query\template\ListPublicTemplatesUseCaseInterface;
use infrastructure\presentation\http\HttpRequest;
use infrastructure\presentation\http\HttpResponse;
use infrastructure\presentation\http\JsonResponse;

final class ListPublicTemplatesController extends AbstractJsonController
{
    public function __construct(
        private readonly ListPublicTemplatesUseCaseInterface $useCase
    ) {
        parent::__construct();
    }

    public function __invoke(HttpRequest $request): HttpResponse
    {
        $results = $this->useCase->execute(new ListPublicTemplatesQuery(
            filters: $this->filters($request, ['engineType', 'name'])
        ));

        return JsonResponse::ok([
            'items' => array_map(static fn ($view): array => $view->toArray(), $results),
        ]);
    }
}
