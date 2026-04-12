<?php

declare(strict_types=1);

namespace application\usecase\command\state;

use application\service\ClockInterface;
use application\service\IdGeneratorInterface;
use domain\state\model\SharedState;
use domain\state\repository\SharedStateRepositoryInterface;

final class SaveStateUseCase implements SaveStateUseCaseInterface
{
    public function __construct(
        private readonly SharedStateRepositoryInterface $stateRepository,
        private readonly IdGeneratorInterface $idGenerator,
        private readonly ClockInterface $clock
    ) {
    }

    public function execute(SaveStateCommand $command): SaveStateResult
    {
        $state = SharedState::create(
            stateId: $this->idGenerator->generate(),
            payload: $command->state,
            createdAt: $this->clock->now(),
            ownerId: $command->ownerId
        );

        $this->stateRepository->save($state);

        return new SaveStateResult(id: $state->stateId);
    }
}
