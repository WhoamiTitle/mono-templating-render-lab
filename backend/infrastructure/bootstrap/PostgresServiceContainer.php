<?php

declare(strict_types=1);

namespace infrastructure\bootstrap;

use DateInterval;
use PDO;
use application\usecase\command\account\LoginUserUseCase;
use application\usecase\command\account\LoginUserUseCaseInterface;
use application\usecase\command\account\LogoutUserUseCase;
use application\usecase\command\account\LogoutUserUseCaseInterface;
use application\usecase\command\account\RegisterUserUseCase;
use application\usecase\command\account\RegisterUserUseCaseInterface;
use application\usecase\command\benchmark_run\CompleteBenchmarkRunFailureUseCase;
use application\usecase\command\benchmark_run\CompleteBenchmarkRunFailureUseCaseInterface;
use application\usecase\command\benchmark_run\CompleteBenchmarkRunSuccessUseCase;
use application\usecase\command\benchmark_run\CompleteBenchmarkRunSuccessUseCaseInterface;
use application\usecase\command\benchmark_run\StartBenchmarkRunUseCase;
use application\usecase\command\benchmark_run\StartBenchmarkRunUseCaseInterface;
use application\usecase\command\render_run\CompleteRenderRunFailureUseCase;
use application\usecase\command\render_run\CompleteRenderRunFailureUseCaseInterface;
use application\usecase\command\render_run\CompleteRenderRunSuccessUseCase;
use application\usecase\command\render_run\CompleteRenderRunSuccessUseCaseInterface;
use application\usecase\command\render_run\StartRenderRunUseCase;
use application\usecase\command\render_run\StartRenderRunUseCaseInterface;
use application\usecase\command\state\SaveStateUseCase;
use application\usecase\command\state\SaveStateUseCaseInterface;
use application\usecase\command\template\CloneTemplateUseCase;
use application\usecase\command\template\CloneTemplateUseCaseInterface;
use application\usecase\command\template\DeactivateTemplateUseCase;
use application\usecase\command\template\DeactivateTemplateUseCaseInterface;
use application\usecase\command\template\RegisterTemplateUseCase;
use application\usecase\command\template\RegisterTemplateUseCaseInterface;
use application\usecase\command\template\UpdateTemplateBodyUseCase;
use application\usecase\command\template\UpdateTemplateBodyUseCaseInterface;
use application\usecase\query\benchmark_run\GetBenchmarkRunUseCase;
use application\usecase\query\benchmark_run\GetBenchmarkRunUseCaseInterface;
use application\usecase\query\benchmark_run\ListBenchmarkRunsUseCase;
use application\usecase\query\benchmark_run\ListBenchmarkRunsUseCaseInterface;
use application\usecase\query\render_run\GetRecentFailuresUseCase;
use application\usecase\query\render_run\GetRecentFailuresUseCaseInterface;
use application\usecase\query\render_run\GetRenderRunUseCase;
use application\usecase\query\render_run\GetRenderRunUseCaseInterface;
use application\usecase\query\render_run\ListRenderRunsUseCase;
use application\usecase\query\render_run\ListRenderRunsUseCaseInterface;
use application\usecase\query\state\GetStateUseCase;
use application\usecase\query\state\GetStateUseCaseInterface;
use application\usecase\query\template\GetTemplateStatsUseCase;
use application\usecase\query\template\GetTemplateStatsUseCaseInterface;
use application\usecase\query\template\GetTemplateUseCase;
use application\usecase\query\template\GetTemplateUseCaseInterface;
use application\usecase\query\template\ListPublicTemplatesUseCase;
use application\usecase\query\template\ListPublicTemplatesUseCaseInterface;
use application\usecase\query\template\ListTemplatesUseCase;
use application\usecase\query\template\ListTemplatesUseCaseInterface;
use domain\account\repository\AuthSessionRepositoryInterface;
use domain\account\repository\PasswordResetTokenRepositoryInterface;
use domain\account\repository\UserRepositoryInterface;
use domain\benchmark_run\repository\BenchmarkRunRepositoryInterface;
use domain\render_run\repository\RenderRunRepositoryInterface;
use domain\state\repository\SharedStateRepositoryInterface;
use domain\template\repository\TemplateRepositoryInterface;
use infrastructure\presentation\http\controller\CompleteBenchmarkRunFailureController;
use infrastructure\presentation\http\controller\CompleteBenchmarkRunSuccessController;
use infrastructure\presentation\http\controller\CompleteRenderRunFailureController;
use infrastructure\presentation\http\controller\CompleteRenderRunSuccessController;
use infrastructure\presentation\http\controller\CloneTemplateController;
use infrastructure\presentation\http\controller\DeactivateTemplateController;
use infrastructure\presentation\http\controller\GetBenchmarkRunController;
use infrastructure\presentation\http\controller\GetRecentFailuresController;
use infrastructure\presentation\http\controller\GetRenderRunController;
use infrastructure\presentation\http\controller\GetStateController;
use infrastructure\presentation\http\controller\GetTemplateController;
use infrastructure\presentation\http\controller\GetTemplateStatsController;
use infrastructure\presentation\http\controller\LoginUserController;
use infrastructure\presentation\http\controller\ListBenchmarkRunsController;
use infrastructure\presentation\http\controller\ListPublicTemplatesController;
use infrastructure\presentation\http\controller\ListRenderRunsController;
use infrastructure\presentation\http\controller\ListTemplatesController;
use infrastructure\presentation\http\controller\LogoutUserController;
use infrastructure\presentation\http\controller\RegisterTemplateController;
use infrastructure\presentation\http\controller\RegisterUserController;
use infrastructure\presentation\http\controller\SaveStateController;
use infrastructure\presentation\http\controller\StartBenchmarkRunController;
use infrastructure\presentation\http\controller\StartRenderRunController;
use infrastructure\presentation\http\controller\UpdateTemplateBodyController;
use infrastructure\presentation\http\route\CommandRoutes;
use infrastructure\repository\postgres\PostgresAuthSessionRepository;
use infrastructure\repository\postgres\PostgresBenchmarkRunRepository;
use infrastructure\repository\postgres\PostgresConnectionFactory;
use infrastructure\repository\postgres\PostgresPasswordResetTokenRepository;
use infrastructure\repository\postgres\PostgresRenderRunRepository;
use infrastructure\repository\postgres\PostgresSharedStateRepository;
use infrastructure\repository\postgres\PostgresTemplateRepository;
use infrastructure\repository\postgres\PostgresUserRepository;
use infrastructure\support\NativePasswordHasher;
use infrastructure\support\SystemClock;
use infrastructure\support\UuidV4Generator;

final class PostgresServiceContainer
{
    /**
     * @var array<string, object>
     */
    private array $services = [];

    public function __construct(
        private readonly PostgresConfig $config
    ) {
    }

    public function commandController(string $className): object
    {
        return match ($className) {
            RegisterTemplateController::class => $this->get(RegisterTemplateController::class, fn () => new RegisterTemplateController($this->registerTemplateUseCase())),
            ListTemplatesController::class => $this->get(ListTemplatesController::class, fn () => new ListTemplatesController($this->listTemplatesUseCase())),
            ListPublicTemplatesController::class => $this->get(ListPublicTemplatesController::class, fn () => new ListPublicTemplatesController($this->listPublicTemplatesUseCase())),
            GetTemplateController::class => $this->get(GetTemplateController::class, fn () => new GetTemplateController($this->getTemplateUseCase())),
            GetTemplateStatsController::class => $this->get(GetTemplateStatsController::class, fn () => new GetTemplateStatsController($this->getTemplateStatsUseCase())),
            UpdateTemplateBodyController::class => $this->get(UpdateTemplateBodyController::class, fn () => new UpdateTemplateBodyController($this->updateTemplateBodyUseCase())),
            DeactivateTemplateController::class => $this->get(DeactivateTemplateController::class, fn () => new DeactivateTemplateController($this->deactivateTemplateUseCase())),
            CloneTemplateController::class => $this->get(CloneTemplateController::class, fn () => new CloneTemplateController($this->cloneTemplateUseCase())),
            ListRenderRunsController::class => $this->get(ListRenderRunsController::class, fn () => new ListRenderRunsController($this->listRenderRunsUseCase())),
            StartRenderRunController::class => $this->get(StartRenderRunController::class, fn () => new StartRenderRunController($this->startRenderRunUseCase())),
            GetRenderRunController::class => $this->get(GetRenderRunController::class, fn () => new GetRenderRunController($this->getRenderRunUseCase())),
            GetRecentFailuresController::class => $this->get(GetRecentFailuresController::class, fn () => new GetRecentFailuresController($this->getRecentFailuresUseCase())),
            CompleteRenderRunSuccessController::class => $this->get(CompleteRenderRunSuccessController::class, fn () => new CompleteRenderRunSuccessController($this->completeRenderRunSuccessUseCase())),
            CompleteRenderRunFailureController::class => $this->get(CompleteRenderRunFailureController::class, fn () => new CompleteRenderRunFailureController($this->completeRenderRunFailureUseCase())),
            ListBenchmarkRunsController::class => $this->get(ListBenchmarkRunsController::class, fn () => new ListBenchmarkRunsController($this->listBenchmarkRunsUseCase())),
            StartBenchmarkRunController::class => $this->get(StartBenchmarkRunController::class, fn () => new StartBenchmarkRunController($this->startBenchmarkRunUseCase())),
            GetBenchmarkRunController::class => $this->get(GetBenchmarkRunController::class, fn () => new GetBenchmarkRunController($this->getBenchmarkRunUseCase())),
            CompleteBenchmarkRunSuccessController::class => $this->get(CompleteBenchmarkRunSuccessController::class, fn () => new CompleteBenchmarkRunSuccessController($this->completeBenchmarkRunSuccessUseCase())),
            CompleteBenchmarkRunFailureController::class => $this->get(CompleteBenchmarkRunFailureController::class, fn () => new CompleteBenchmarkRunFailureController($this->completeBenchmarkRunFailureUseCase())),
            RegisterUserController::class => $this->get(RegisterUserController::class, fn () => new RegisterUserController($this->registerUserUseCase())),
            LoginUserController::class => $this->get(LoginUserController::class, fn () => new LoginUserController($this->loginUserUseCase())),
            LogoutUserController::class => $this->get(LogoutUserController::class, fn () => new LogoutUserController($this->logoutUserUseCase())),
            SaveStateController::class => $this->get(SaveStateController::class, fn () => new SaveStateController($this->saveStateUseCase())),
            GetStateController::class => $this->get(GetStateController::class, fn () => new GetStateController($this->getStateUseCase())),
            default => throw new \InvalidArgumentException('Unknown controller: ' . $className),
        };
    }

    /**
     * @return array<int, array{method: string, path: string, controller: object, requiresAuth: bool}>
     */
    public function commandRoutes(): array
    {
        $resolved = [];

        foreach (CommandRoutes::definitions() as $definition) {
            $resolved[] = [
                'method' => $definition['method'],
                'path' => $definition['path'],
                'controller' => $this->commandController($definition['controller']),
                'requiresAuth' => (bool)($definition['requiresAuth'] ?? true),
            ];
        }

        return $resolved;
    }

    public function registerTemplateUseCase(): RegisterTemplateUseCaseInterface
    {
        return $this->get(RegisterTemplateUseCaseInterface::class, fn () => new RegisterTemplateUseCase(
            $this->templateRepository(),
            $this->idGenerator(),
            $this->clock()
        ));
    }

    public function getTemplateUseCase(): GetTemplateUseCaseInterface
    {
        return $this->get(GetTemplateUseCaseInterface::class, fn () => new GetTemplateUseCase(
            $this->templateRepository()
        ));
    }

    public function listTemplatesUseCase(): ListTemplatesUseCaseInterface
    {
        return $this->get(ListTemplatesUseCaseInterface::class, fn () => new ListTemplatesUseCase(
            $this->templateRepository()
        ));
    }

    public function listPublicTemplatesUseCase(): ListPublicTemplatesUseCaseInterface
    {
        return $this->get(ListPublicTemplatesUseCaseInterface::class, fn () => new ListPublicTemplatesUseCase(
            $this->templateRepository()
        ));
    }

    public function getTemplateStatsUseCase(): GetTemplateStatsUseCaseInterface
    {
        return $this->get(GetTemplateStatsUseCaseInterface::class, fn () => new GetTemplateStatsUseCase(
            $this->templateRepository(),
            $this->renderRunRepository()
        ));
    }

    public function updateTemplateBodyUseCase(): UpdateTemplateBodyUseCaseInterface
    {
        return $this->get(UpdateTemplateBodyUseCaseInterface::class, fn () => new UpdateTemplateBodyUseCase(
            $this->templateRepository(),
            $this->clock()
        ));
    }

    public function deactivateTemplateUseCase(): DeactivateTemplateUseCaseInterface
    {
        return $this->get(DeactivateTemplateUseCaseInterface::class, fn () => new DeactivateTemplateUseCase(
            $this->templateRepository(),
            $this->clock()
        ));
    }

    public function cloneTemplateUseCase(): CloneTemplateUseCaseInterface
    {
        return $this->get(CloneTemplateUseCaseInterface::class, fn () => new CloneTemplateUseCase(
            $this->templateRepository(),
            $this->idGenerator(),
            $this->clock()
        ));
    }

    public function startRenderRunUseCase(): StartRenderRunUseCaseInterface
    {
        return $this->get(StartRenderRunUseCaseInterface::class, fn () => new StartRenderRunUseCase(
            $this->templateRepository(),
            $this->renderRunRepository(),
            $this->idGenerator(),
            $this->clock()
        ));
    }

    public function getRenderRunUseCase(): GetRenderRunUseCaseInterface
    {
        return $this->get(GetRenderRunUseCaseInterface::class, fn () => new GetRenderRunUseCase(
            $this->renderRunRepository()
        ));
    }

    public function listRenderRunsUseCase(): ListRenderRunsUseCaseInterface
    {
        return $this->get(ListRenderRunsUseCaseInterface::class, fn () => new ListRenderRunsUseCase(
            $this->renderRunRepository()
        ));
    }

    public function getRecentFailuresUseCase(): GetRecentFailuresUseCaseInterface
    {
        return $this->get(GetRecentFailuresUseCaseInterface::class, fn () => new GetRecentFailuresUseCase(
            $this->renderRunRepository()
        ));
    }

    public function completeRenderRunSuccessUseCase(): CompleteRenderRunSuccessUseCaseInterface
    {
        return $this->get(CompleteRenderRunSuccessUseCaseInterface::class, fn () => new CompleteRenderRunSuccessUseCase(
            $this->renderRunRepository(),
            $this->clock()
        ));
    }

    public function completeRenderRunFailureUseCase(): CompleteRenderRunFailureUseCaseInterface
    {
        return $this->get(CompleteRenderRunFailureUseCaseInterface::class, fn () => new CompleteRenderRunFailureUseCase(
            $this->renderRunRepository(),
            $this->clock()
        ));
    }

    public function startBenchmarkRunUseCase(): StartBenchmarkRunUseCaseInterface
    {
        return $this->get(StartBenchmarkRunUseCaseInterface::class, fn () => new StartBenchmarkRunUseCase(
            $this->templateRepository(),
            $this->benchmarkRunRepository(),
            $this->idGenerator(),
            $this->clock()
        ));
    }

    public function getBenchmarkRunUseCase(): GetBenchmarkRunUseCaseInterface
    {
        return $this->get(GetBenchmarkRunUseCaseInterface::class, fn () => new GetBenchmarkRunUseCase(
            $this->benchmarkRunRepository()
        ));
    }

    public function listBenchmarkRunsUseCase(): ListBenchmarkRunsUseCaseInterface
    {
        return $this->get(ListBenchmarkRunsUseCaseInterface::class, fn () => new ListBenchmarkRunsUseCase(
            $this->benchmarkRunRepository()
        ));
    }

    public function completeBenchmarkRunSuccessUseCase(): CompleteBenchmarkRunSuccessUseCaseInterface
    {
        return $this->get(CompleteBenchmarkRunSuccessUseCaseInterface::class, fn () => new CompleteBenchmarkRunSuccessUseCase(
            $this->benchmarkRunRepository(),
            $this->clock()
        ));
    }

    public function completeBenchmarkRunFailureUseCase(): CompleteBenchmarkRunFailureUseCaseInterface
    {
        return $this->get(CompleteBenchmarkRunFailureUseCaseInterface::class, fn () => new CompleteBenchmarkRunFailureUseCase(
            $this->benchmarkRunRepository(),
            $this->clock()
        ));
    }

    public function registerUserUseCase(): RegisterUserUseCaseInterface
    {
        return $this->get(RegisterUserUseCaseInterface::class, fn () => new RegisterUserUseCase(
            $this->userRepository(),
            $this->idGenerator(),
            $this->passwordHasher(),
            $this->clock()
        ));
    }

    public function loginUserUseCase(): LoginUserUseCaseInterface
    {
        return $this->get(LoginUserUseCaseInterface::class, fn () => new LoginUserUseCase(
            $this->userRepository(),
            $this->authSessionRepository(),
            $this->passwordHasher(),
            $this->idGenerator(),
            $this->clock(),
            $this->sessionTtl()
        ));
    }

    public function logoutUserUseCase(): LogoutUserUseCaseInterface
    {
        return $this->get(LogoutUserUseCaseInterface::class, fn () => new LogoutUserUseCase(
            $this->authSessionRepository(),
            $this->clock()
        ));
    }

    public function saveStateUseCase(): SaveStateUseCaseInterface
    {
        return $this->get(SaveStateUseCaseInterface::class, fn () => new SaveStateUseCase(
            $this->stateRepository(),
            $this->idGenerator(),
            $this->clock()
        ));
    }

    public function getStateUseCase(): GetStateUseCaseInterface
    {
        return $this->get(GetStateUseCaseInterface::class, fn () => new GetStateUseCase(
            $this->stateRepository()
        ));
    }

    public function templateRepository(): TemplateRepositoryInterface
    {
        return $this->get(TemplateRepositoryInterface::class, fn () => new PostgresTemplateRepository($this->connection()));
    }

    public function renderRunRepository(): RenderRunRepositoryInterface
    {
        return $this->get(RenderRunRepositoryInterface::class, fn () => new PostgresRenderRunRepository($this->connection()));
    }

    public function benchmarkRunRepository(): BenchmarkRunRepositoryInterface
    {
        return $this->get(BenchmarkRunRepositoryInterface::class, fn () => new PostgresBenchmarkRunRepository($this->connection()));
    }

    public function userRepository(): UserRepositoryInterface
    {
        return $this->get(UserRepositoryInterface::class, fn () => new PostgresUserRepository($this->connection()));
    }

    public function authSessionRepository(): AuthSessionRepositoryInterface
    {
        return $this->get(AuthSessionRepositoryInterface::class, fn () => new PostgresAuthSessionRepository($this->connection()));
    }

    public function passwordResetTokenRepository(): PasswordResetTokenRepositoryInterface
    {
        return $this->get(PasswordResetTokenRepositoryInterface::class, fn () => new PostgresPasswordResetTokenRepository($this->connection()));
    }

    public function stateRepository(): SharedStateRepositoryInterface
    {
        return $this->get(SharedStateRepositoryInterface::class, fn () => new PostgresSharedStateRepository($this->connection()));
    }

    private function connection(): PDO
    {
        return $this->get(PDO::class, fn () => PostgresConnectionFactory::create([
            'host' => $this->config->host,
            'port' => $this->config->port,
            'dbname' => $this->config->dbname,
            'user' => $this->config->user,
            'password' => $this->config->password,
            'sslmode' => $this->config->sslmode,
        ]));
    }

    private function clock(): SystemClock
    {
        return $this->get(SystemClock::class, fn () => new SystemClock());
    }

    private function idGenerator(): UuidV4Generator
    {
        return $this->get(UuidV4Generator::class, fn () => new UuidV4Generator());
    }

    private function passwordHasher(): NativePasswordHasher
    {
        return $this->get(NativePasswordHasher::class, fn () => new NativePasswordHasher());
    }

    private function sessionTtl(): DateInterval
    {
        return $this->get(DateInterval::class, fn () => new DateInterval($this->config->sessionTtlSpec));
    }

    /**
     * @template T of object
     * @param class-string<T> $key
     * @param \Closure():T $factory
     * @return T
     */
    private function get(string $key, \Closure $factory): object
    {
        if (!isset($this->services[$key])) {
            $this->services[$key] = $factory();
        }

        return $this->services[$key];
    }
}
