<?php

declare(strict_types=1);

namespace application\usecase\query\state;

use application\usecase\exception\ResourceNotFoundException;
use domain\state\repository\SharedStateRepositoryInterface;

final class GetStateUseCase implements GetStateUseCaseInterface
{
    public function __construct(
        private readonly SharedStateRepositoryInterface $stateRepository
    ) {
    }

    public function execute(GetStateQuery $query): StateView
    {
        $state = $this->stateRepository->getById($query->stateId);
        if ($state === null) {
            throw new ResourceNotFoundException('state.not_found: ' . $query->stateId);
        }

        return new StateView(state: $state->payload);
    }
}
