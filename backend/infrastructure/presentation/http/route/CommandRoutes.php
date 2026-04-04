<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\route;

use infrastructure\presentation\http\controller\CompleteBenchmarkRunFailureController;
use infrastructure\presentation\http\controller\CompleteBenchmarkRunSuccessController;
use infrastructure\presentation\http\controller\CompleteRenderRunFailureController;
use infrastructure\presentation\http\controller\CompleteRenderRunSuccessController;
use infrastructure\presentation\http\controller\DeactivateTemplateController;
use infrastructure\presentation\http\controller\GetBenchmarkRunController;
use infrastructure\presentation\http\controller\GetRecentFailuresController;
use infrastructure\presentation\http\controller\GetRenderRunController;
use infrastructure\presentation\http\controller\GetTemplateController;
use infrastructure\presentation\http\controller\GetTemplateStatsController;
use infrastructure\presentation\http\controller\LoginUserController;
use infrastructure\presentation\http\controller\ListBenchmarkRunsController;
use infrastructure\presentation\http\controller\ListRenderRunsController;
use infrastructure\presentation\http\controller\ListTemplatesController;
use infrastructure\presentation\http\controller\LogoutUserController;
use infrastructure\presentation\http\controller\RegisterUserController;
use infrastructure\presentation\http\controller\RegisterTemplateController;
use infrastructure\presentation\http\controller\StartBenchmarkRunController;
use infrastructure\presentation\http\controller\StartRenderRunController;
use infrastructure\presentation\http\controller\UpdateTemplateBodyController;

final class CommandRoutes
{
    /**
     * @return array<int, array{method: string, path: string, controller: class-string}>
     */
    public static function definitions(): array
    {
        return [
            ['method' => 'GET', 'path' => '/templates', 'controller' => ListTemplatesController::class],
            ['method' => 'POST', 'path' => '/templates', 'controller' => RegisterTemplateController::class],
            ['method' => 'GET', 'path' => '/templates/{templateId}', 'controller' => GetTemplateController::class],
            ['method' => 'GET', 'path' => '/templates/{templateId}/stats', 'controller' => GetTemplateStatsController::class],
            ['method' => 'PUT', 'path' => '/templates/{templateId}/body', 'controller' => UpdateTemplateBodyController::class],
            ['method' => 'POST', 'path' => '/templates/{templateId}/deactivation', 'controller' => DeactivateTemplateController::class],
            ['method' => 'GET', 'path' => '/render-runs', 'controller' => ListRenderRunsController::class],
            ['method' => 'POST', 'path' => '/render-runs', 'controller' => StartRenderRunController::class],
            ['method' => 'GET', 'path' => '/render-runs/failures/recent', 'controller' => GetRecentFailuresController::class],
            ['method' => 'GET', 'path' => '/render-runs/{runId}', 'controller' => GetRenderRunController::class],
            ['method' => 'POST', 'path' => '/render-runs/{runId}/success', 'controller' => CompleteRenderRunSuccessController::class],
            ['method' => 'POST', 'path' => '/render-runs/{runId}/failure', 'controller' => CompleteRenderRunFailureController::class],
            ['method' => 'GET', 'path' => '/benchmark-runs', 'controller' => ListBenchmarkRunsController::class],
            ['method' => 'POST', 'path' => '/benchmark-runs', 'controller' => StartBenchmarkRunController::class],
            ['method' => 'GET', 'path' => '/benchmark-runs/{benchmarkRunId}', 'controller' => GetBenchmarkRunController::class],
            ['method' => 'POST', 'path' => '/benchmark-runs/{benchmarkRunId}/success', 'controller' => CompleteBenchmarkRunSuccessController::class],
            ['method' => 'POST', 'path' => '/benchmark-runs/{benchmarkRunId}/failure', 'controller' => CompleteBenchmarkRunFailureController::class],
            ['method' => 'POST', 'path' => '/users', 'controller' => RegisterUserController::class],
            ['method' => 'POST', 'path' => '/sessions', 'controller' => LoginUserController::class],
            ['method' => 'DELETE', 'path' => '/sessions/current', 'controller' => LogoutUserController::class],
        ];
    }
}
