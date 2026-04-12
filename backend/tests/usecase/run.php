<?php

declare(strict_types=1);

use application\usecase\command\account\LoginUserCommand;
use application\usecase\command\account\LoginUserUseCase;
use application\usecase\command\account\LogoutUserCommand;
use application\usecase\command\account\LogoutUserUseCase;
use application\usecase\command\account\RegisterUserCommand;
use application\usecase\command\account\RegisterUserUseCase;
use application\usecase\command\benchmark_run\CompleteBenchmarkRunFailureCommand;
use application\usecase\command\benchmark_run\CompleteBenchmarkRunFailureUseCase;
use application\usecase\command\benchmark_run\CompleteBenchmarkRunSuccessCommand;
use application\usecase\command\benchmark_run\CompleteBenchmarkRunSuccessUseCase;
use application\usecase\command\benchmark_run\StartBenchmarkRunCommand;
use application\usecase\command\benchmark_run\StartBenchmarkRunUseCase;
use application\usecase\command\render_run\CompleteRenderRunFailureCommand;
use application\usecase\command\render_run\CompleteRenderRunFailureUseCase;
use application\usecase\command\render_run\CompleteRenderRunSuccessCommand;
use application\usecase\command\render_run\CompleteRenderRunSuccessUseCase;
use application\usecase\command\render_run\StartRenderRunCommand;
use application\usecase\command\render_run\StartRenderRunUseCase;
use application\usecase\command\template\RegisterTemplateCommand;
use application\usecase\command\template\RegisterTemplateUseCase;
use application\usecase\query\benchmark_run\GetBenchmarkRunQuery;
use application\usecase\query\benchmark_run\GetBenchmarkRunUseCase;
use application\usecase\query\benchmark_run\ListBenchmarkRunsQuery;
use application\usecase\query\benchmark_run\ListBenchmarkRunsUseCase;
use application\usecase\query\render_run\GetRecentFailuresQuery;
use application\usecase\query\render_run\GetRecentFailuresUseCase;
use application\usecase\query\render_run\GetRenderRunQuery;
use application\usecase\query\render_run\GetRenderRunUseCase;
use application\usecase\query\render_run\ListRenderRunsQuery;
use application\usecase\query\render_run\ListRenderRunsUseCase;
use application\usecase\query\template\GetTemplateQuery;
use application\usecase\query\template\GetTemplateStatsQuery;
use application\usecase\query\template\GetTemplateStatsUseCase;
use application\usecase\query\template\GetTemplateUseCase;
use application\usecase\query\template\ListTemplatesQuery;
use application\usecase\query\template\ListTemplatesUseCase;
use infrastructure\repository\in_memory\InMemoryAuthSessionRepository;
use infrastructure\repository\in_memory\InMemoryBenchmarkRunRepository;
use infrastructure\repository\in_memory\InMemoryRenderRunRepository;
use infrastructure\repository\in_memory\InMemoryTemplateRepository;
use infrastructure\repository\in_memory\InMemoryUserRepository;
use infrastructure\support\NativePasswordHasher;
use infrastructure\support\SystemClock;
use infrastructure\support\UuidV4Generator;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function assert_same(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(sprintf('%s. Expected: %s. Actual: %s', $message, var_export($expected, true), var_export($actual, true)));
    }
}

function print_payload(string $label, array $payload): void
{
    echo '[' . $label . ']' . PHP_EOL;
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL . PHP_EOL;
}

function measure_duration_ms(callable $work): int
{
    $startedAt = microtime(true);
    $work();
    $elapsed = (int)round((microtime(true) - $startedAt) * 1000);

    return max(1, $elapsed);
}

function build_benchmark_samples(int $iterationsN, callable $work): array
{
    $samples = [];

    for ($i = 0; $i < $iterationsN; $i++) {
        $samples[] = measure_duration_ms($work);
    }

    return $samples;
}

function benchmark_stats(array $samples): array
{
    if ($samples === []) {
        throw new RuntimeException('Benchmark samples are empty');
    }

    $sorted = $samples;
    sort($sorted);

    $count = count($sorted);
    $avg = array_sum($sorted) / $count;
    $p95Index = max(0, min($count - 1, (int)ceil(0.95 * $count) - 1));

    return [
        'avgMs' => round($avg, 3),
        'minMs' => min($sorted),
        'maxMs' => max($sorted),
        'p95Ms' => $sorted[$p95Index],
    ];
}

$userRepository = new InMemoryUserRepository();
$authSessionRepository = new InMemoryAuthSessionRepository();
$templateRepository = new InMemoryTemplateRepository();
$renderRunRepository = new InMemoryRenderRunRepository();
$benchmarkRunRepository = new InMemoryBenchmarkRunRepository();

$clock = new SystemClock();
$idGenerator = new UuidV4Generator();
$passwordHasher = new NativePasswordHasher();

$registerUserUseCase = new RegisterUserUseCase(
    $userRepository,
    $idGenerator,
    $passwordHasher,
    $clock
);
$loginUserUseCase = new LoginUserUseCase(
    $userRepository,
    $authSessionRepository,
    $passwordHasher,
    $idGenerator,
    $clock,
    new DateInterval('P7D')
);
$logoutUserUseCase = new LogoutUserUseCase(
    $authSessionRepository,
    $clock
);
$registerTemplateUseCase = new RegisterTemplateUseCase(
    $templateRepository,
    $idGenerator,
    $clock
);
$startRenderRunUseCase = new StartRenderRunUseCase(
    $templateRepository,
    $renderRunRepository,
    $idGenerator,
    $clock
);
$completeRenderRunSuccessUseCase = new CompleteRenderRunSuccessUseCase(
    $renderRunRepository,
    $clock
);
$completeRenderRunFailureUseCase = new CompleteRenderRunFailureUseCase(
    $renderRunRepository,
    $clock
);
$startBenchmarkRunUseCase = new StartBenchmarkRunUseCase(
    $templateRepository,
    $benchmarkRunRepository,
    $idGenerator,
    $clock
);
$completeBenchmarkRunSuccessUseCase = new CompleteBenchmarkRunSuccessUseCase(
    $benchmarkRunRepository,
    $clock
);
$completeBenchmarkRunFailureUseCase = new CompleteBenchmarkRunFailureUseCase(
    $benchmarkRunRepository,
    $clock
);

$getTemplateUseCase = new GetTemplateUseCase($templateRepository);
$listTemplatesUseCase = new ListTemplatesUseCase($templateRepository);
$getTemplateStatsUseCase = new GetTemplateStatsUseCase($templateRepository, $renderRunRepository);

$getRenderRunUseCase = new GetRenderRunUseCase($renderRunRepository);
$listRenderRunsUseCase = new ListRenderRunsUseCase($renderRunRepository);
$getRecentFailuresUseCase = new GetRecentFailuresUseCase($renderRunRepository);

$getBenchmarkRunUseCase = new GetBenchmarkRunUseCase($benchmarkRunRepository);
$listBenchmarkRunsUseCase = new ListBenchmarkRunsUseCase($benchmarkRunRepository);

$email = sprintf('usecase_%s@example.com', bin2hex(random_bytes(4)));
$password = 'Passw0rd!123';
$templateBody = 'Hello {{name}}';

$registeredUser = $registerUserUseCase->execute(new RegisterUserCommand(
    email: $email,
    password: $password
));
print_payload('usecase.register_user.result', $registeredUser->toArray());
$actorId = $registeredUser->userId;

$loginResult = $loginUserUseCase->execute(new LoginUserCommand(
    email: $email,
    password: $password
));
print_payload('usecase.login_user.result', $loginResult->toArray());
assert_true($loginResult->sessionId !== '', 'Login session id must not be empty');

$registeredTemplate = $registerTemplateUseCase->execute(new RegisterTemplateCommand(
    actorId: $actorId,
    name: 'Usecase template',
    engineType: 'handlebars',
    templateBody: $templateBody
));
print_payload('usecase.register_template.result', $registeredTemplate->toArray());
$templateId = $registeredTemplate->templateId;

$startedRenderRunSuccess = $startRenderRunUseCase->execute(new StartRenderRunCommand(
    actorId: $actorId,
    templateId: $templateId,
    contextJson: ['name' => 'Usecase Success']
));
print_payload('usecase.start_render_run.success.result', $startedRenderRunSuccess->toArray());

$renderOutputText = str_replace('{{name}}', 'Usecase Success', $templateBody);
$renderDurationMs = measure_duration_ms(static function () use ($templateBody): void {
    $acc = 0;
    for ($i = 0; $i < 1200000; $i++) {
        $acc += (($i * 19) % 101);
    }

    str_replace('{{name}}', 'Usecase Success', $templateBody);
});

$completeRenderSuccess = $completeRenderRunSuccessUseCase->execute(new CompleteRenderRunSuccessCommand(
    actorId: $actorId,
    runId: $startedRenderRunSuccess->runId,
    durationMs: $renderDurationMs,
    outputText: $renderOutputText
));
print_payload('usecase.complete_render_run.success.result', $completeRenderSuccess->toArray());

$startedRenderRunFailure = $startRenderRunUseCase->execute(new StartRenderRunCommand(
    actorId: $actorId,
    templateId: $templateId,
    contextJson: ['name' => 'Usecase Failure']
));
print_payload('usecase.start_render_run.failure.result', $startedRenderRunFailure->toArray());

$renderFailureDurationMs = measure_duration_ms(static function (): void {
    $acc = 0;
    for ($i = 0; $i < 1500000; $i++) {
        $acc += ($i % 13);
    }
});

$completeRenderFailure = $completeRenderRunFailureUseCase->execute(new CompleteRenderRunFailureCommand(
    actorId: $actorId,
    runId: $startedRenderRunFailure->runId,
    durationMs: $renderFailureDurationMs,
    errorCode: 'RENDER_ERR',
    errorMessage: 'Simulated render failure'
));
print_payload('usecase.complete_render_run.failure.result', $completeRenderFailure->toArray());

$renderRunView = $getRenderRunUseCase->execute(new GetRenderRunQuery(
    actorId: $actorId,
    runId: $startedRenderRunSuccess->runId
));
print_payload('usecase.get_render_run.view', $renderRunView->toArray());
assert_same('success', $renderRunView->status, 'Render run status mismatch');
assert_same($renderOutputText, $renderRunView->outputText, 'Render output mismatch');

$allRenderRuns = $listRenderRunsUseCase->execute(new ListRenderRunsQuery(actorId: $actorId));
print_payload('usecase.list_render_runs.view', [
    'items' => array_map(static fn ($item): array => $item->toArray(), $allRenderRuns),
]);
assert_true(count($allRenderRuns) >= 2, 'Expected at least 2 render runs');

$recentFailures = $getRecentFailuresUseCase->execute(new GetRecentFailuresQuery(actorId: $actorId, limit: 10));
print_payload('usecase.get_recent_failures.view', [
    'items' => array_map(static fn ($item): array => $item->toArray(), $recentFailures),
]);
assert_true(count($recentFailures) >= 1, 'Expected at least 1 failed render run');

$iterationsN = 7;
$startedBenchmarkRunSuccess = $startBenchmarkRunUseCase->execute(new StartBenchmarkRunCommand(
    actorId: $actorId,
    templateId: $templateId,
    contextJson: ['name' => 'Usecase Bench'],
    iterationsN: $iterationsN
));
print_payload('usecase.start_benchmark_run.success.result', $startedBenchmarkRunSuccess->toArray());

$benchmarkOutputText = str_replace('{{name}}', 'Usecase Bench', $templateBody);
$benchmarkSamples = build_benchmark_samples($iterationsN, static function () use ($templateBody): void {
    $acc = 0;
    for ($i = 0; $i < 1000000; $i++) {
        $acc += (($i * 17) % 113);
    }

    str_replace('{{name}}', 'Usecase Bench', $templateBody);
});
$benchmarkStats = benchmark_stats($benchmarkSamples);
print_payload('usecase.benchmark.generated_metrics', [
    'samplesMs' => $benchmarkSamples,
    'avgMs' => $benchmarkStats['avgMs'],
    'minMs' => $benchmarkStats['minMs'],
    'maxMs' => $benchmarkStats['maxMs'],
    'p95Ms' => $benchmarkStats['p95Ms'],
    'outputBytes' => strlen($benchmarkOutputText),
]);

$completeBenchmarkSuccess = $completeBenchmarkRunSuccessUseCase->execute(new CompleteBenchmarkRunSuccessCommand(
    actorId: $actorId,
    benchmarkRunId: $startedBenchmarkRunSuccess->benchmarkRunId,
    samplesMs: $benchmarkSamples,
    avgMs: $benchmarkStats['avgMs'],
    minMs: $benchmarkStats['minMs'],
    maxMs: $benchmarkStats['maxMs'],
    p95Ms: $benchmarkStats['p95Ms'],
    outputBytes: strlen($benchmarkOutputText)
));
print_payload('usecase.complete_benchmark_run.success.result', $completeBenchmarkSuccess->toArray());

$startedBenchmarkRunFailure = $startBenchmarkRunUseCase->execute(new StartBenchmarkRunCommand(
    actorId: $actorId,
    templateId: $templateId,
    contextJson: ['name' => 'Usecase Bench Fail'],
    iterationsN: 3
));
print_payload('usecase.start_benchmark_run.failure.result', $startedBenchmarkRunFailure->toArray());

$completeBenchmarkFailure = $completeBenchmarkRunFailureUseCase->execute(new CompleteBenchmarkRunFailureCommand(
    actorId: $actorId,
    benchmarkRunId: $startedBenchmarkRunFailure->benchmarkRunId,
    errorCode: 'BENCH_ERR',
    errorMessage: 'Simulated benchmark failure'
));
print_payload('usecase.complete_benchmark_run.failure.result', $completeBenchmarkFailure->toArray());

$benchmarkRunView = $getBenchmarkRunUseCase->execute(new GetBenchmarkRunQuery(
    actorId: $actorId,
    benchmarkRunId: $startedBenchmarkRunSuccess->benchmarkRunId
));
print_payload('usecase.get_benchmark_run.view', $benchmarkRunView->toArray());
assert_same('success', $benchmarkRunView->status, 'Benchmark run status mismatch');
assert_same($iterationsN, $benchmarkRunView->iterationsN, 'Benchmark iterations mismatch');

$allBenchmarkRuns = $listBenchmarkRunsUseCase->execute(new ListBenchmarkRunsQuery(actorId: $actorId));
print_payload('usecase.list_benchmark_runs.view', [
    'items' => array_map(static fn ($item): array => $item->toArray(), $allBenchmarkRuns),
]);
assert_true(count($allBenchmarkRuns) >= 2, 'Expected at least 2 benchmark runs');

$templateView = $getTemplateUseCase->execute(new GetTemplateQuery(
    actorId: $actorId,
    templateId: $templateId
));
print_payload('usecase.get_template.view', $templateView->toArray());
assert_same('handlebars', $templateView->engineType, 'Template engine mismatch');

$listTemplates = $listTemplatesUseCase->execute(new ListTemplatesQuery(actorId: $actorId));
print_payload('usecase.list_templates.view', [
    'items' => array_map(static fn ($item): array => $item->toArray(), $listTemplates),
]);
assert_true(count($listTemplates) >= 1, 'Expected at least 1 template');

$templateStats = $getTemplateStatsUseCase->execute(new GetTemplateStatsQuery(
    actorId: $actorId,
    templateId: $templateId
));
print_payload('usecase.get_template_stats.view', $templateStats->toArray());
assert_same(2, $templateStats->totalRuns, 'Template total runs mismatch');
assert_same(1, $templateStats->successRuns, 'Template success runs mismatch');
assert_same(1, $templateStats->failedRuns, 'Template failed runs mismatch');

$logoutResult = $logoutUserUseCase->execute(new LogoutUserCommand(
    actorId: $actorId,
    sessionId: $loginResult->sessionId
));
print_payload('usecase.logout_user.result', $logoutResult->toArray());

echo "Use case smoke passed\n";
