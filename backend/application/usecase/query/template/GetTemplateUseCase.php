<?php

declare(strict_types=1);

namespace application\usecase\query\template;

use application\usecase\exception\ResourceNotFoundException;
use domain\template\repository\TemplateRepositoryInterface;

final class GetTemplateUseCase implements GetTemplateUseCaseInterface
{
    public function __construct(
        private readonly TemplateRepositoryInterface $templateRepository
    ) {
    }

    public function execute(GetTemplateQuery $query): TemplateView
    {
        $template = $this->templateRepository->getByIdForOwner($query->templateId, $query->actorId);
        if ($template === null) {
            throw new ResourceNotFoundException('template.not_found: ' . $query->templateId);
        }

        return TemplateViewFactory::fromModel($template);
    }
}
