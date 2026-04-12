<?php

declare(strict_types=1);

namespace application\usecase\query\template;

use domain\template\model\Template;
use domain\template\repository\TemplateRepositoryInterface;

final class ListTemplatesUseCase implements ListTemplatesUseCaseInterface
{
    public function __construct(
        private readonly TemplateRepositoryInterface $templateRepository
    ) {
    }

    public function execute(ListTemplatesQuery $query): array
    {
        $templates = $this->templateRepository->listByOwner($query->actorId, $query->filters);

        return array_map(
            static fn (Template $template): TemplateView => TemplateViewFactory::fromModel($template),
            $templates
        );
    }
}
