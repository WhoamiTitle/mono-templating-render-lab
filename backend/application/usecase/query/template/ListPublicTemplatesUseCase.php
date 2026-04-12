<?php

declare(strict_types=1);

namespace application\usecase\query\template;

use domain\template\model\Template;
use domain\template\repository\TemplateRepositoryInterface;

final class ListPublicTemplatesUseCase implements ListPublicTemplatesUseCaseInterface
{
    public function __construct(
        private readonly TemplateRepositoryInterface $templateRepository
    ) {
    }

    public function execute(ListPublicTemplatesQuery $query): array
    {
        $templates = $this->templateRepository->listPublic($query->filters);

        return array_map(
            static fn (Template $template): TemplateView => TemplateViewFactory::fromModel($template),
            $templates
        );
    }
}
