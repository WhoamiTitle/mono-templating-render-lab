<?php

declare(strict_types=1);

namespace application\usecase\command\benchmark_run;

use application\service\ClockInterface;
use application\service\IdGeneratorInterface;
use application\usecase\exception\ResourceNotFoundException;
use application\usecase\support\IsoDateTime;
use domain\benchmark_run\model\BenchmarkRun;
use domain\benchmark_run\repository\BenchmarkRunRepositoryInterface;
use domain\template\exception\TemplateInactiveException;
use domain\template\repository\TemplateRepositoryInterface;

final class StartBenchmarkRunUseCase implements StartBenchmarkRunUseCaseInterface
{
    public function __construct(
        private readonly TemplateRepositoryInterface $templateRepository,
        private readonly BenchmarkRunRepositoryInterface $benchmarkRunRepository,
        private readonly IdGeneratorInterface $idGenerator,
        private readonly ClockInterface $clock
    ) {
    }

    public function execute(StartBenchmarkRunCommand $command): StartBenchmarkRunResult
    {
        $template = $this->templateRepository->getByIdForOwner($command->templateId, $command->actorId);
        if ($template === null) {
            throw new ResourceNotFoundException('template.not_found: ' . $command->templateId);
        }

        if (!$template->isActive) {
            throw new TemplateInactiveException($template->templateId);
        }

        $benchmarkRun = BenchmarkRun::start(
            benchmarkRunId: $this->idGenerator->generate(),
            ownerId: $template->ownerId,
            templateId: $template->templateId,
            engineType: $template->engineType,
            contextJson: $command->contextJson,
            iterationsN: $command->iterationsN,
            startedAt: $this->clock->now()
        );

        $this->benchmarkRunRepository->save($benchmarkRun);

        return new StartBenchmarkRunResult(
            benchmarkRunId: $benchmarkRun->benchmarkRunId,
            templateId: $benchmarkRun->templateId,
            ownerId: $benchmarkRun->ownerId,
            status: $benchmarkRun->status,
            iterationsN: $benchmarkRun->iterationsN,
            startedAt: IsoDateTime::format($benchmarkRun->startedAt)
        );
    }
}
