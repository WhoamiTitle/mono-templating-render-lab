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
use infrastructure\presentation\http\controller\GetStateController;
use infrastructure\presentation\http\controller\GetTemplateController;
use infrastructure\presentation\http\controller\GetTemplateStatsController;
use infrastructure\presentation\http\controller\LoginUserController;
use infrastructure\presentation\http\controller\ListBenchmarkRunsController;
use infrastructure\presentation\http\controller\ListRenderRunsController;
use infrastructure\presentation\http\controller\ListTemplatesController;
use infrastructure\presentation\http\controller\LogoutUserController;
use infrastructure\presentation\http\controller\OpenApiJsonController;
use infrastructure\presentation\http\controller\RegisterUserController;
use infrastructure\presentation\http\controller\SaveStateController;
use infrastructure\presentation\http\controller\RegisterTemplateController;
use infrastructure\presentation\http\controller\StartBenchmarkRunController;
use infrastructure\presentation\http\controller\StartRenderRunController;
use infrastructure\presentation\http\controller\SwaggerUiController;
use infrastructure\presentation\http\controller\UpdateTemplateBodyController;

final class CommandRoutes
{
    /**
     * @return class-string[]
     */
    public static function controllerClasses(): array
    {
        return [
            ListTemplatesController::class,
            RegisterTemplateController::class,
            GetTemplateController::class,
            GetTemplateStatsController::class,
            UpdateTemplateBodyController::class,
            DeactivateTemplateController::class,
            ListRenderRunsController::class,
            StartRenderRunController::class,
            GetRecentFailuresController::class,
            GetRenderRunController::class,
            CompleteRenderRunSuccessController::class,
            CompleteRenderRunFailureController::class,
            ListBenchmarkRunsController::class,
            StartBenchmarkRunController::class,
            GetBenchmarkRunController::class,
            CompleteBenchmarkRunSuccessController::class,
            CompleteBenchmarkRunFailureController::class,
            SaveStateController::class,
            GetStateController::class,
            RegisterUserController::class,
            LoginUserController::class,
            LogoutUserController::class,
            OpenApiJsonController::class,
            SwaggerUiController::class,
        ];
    }

    /**
     * @return array<int, array{method: string, path: string, controller: class-string}>
     */
    public static function definitions(): array
    {
        return (new AttributeRouteScanner())->definitions(self::controllerClasses());
    }
}
