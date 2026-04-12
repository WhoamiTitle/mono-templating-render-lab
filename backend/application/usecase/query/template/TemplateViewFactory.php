<?php

declare(strict_types=1);

namespace application\usecase\query\template;

use application\usecase\support\IsoDateTime;
use domain\template\model\Template;

final class TemplateViewFactory
{
    public static function fromModel(Template $template): TemplateView
    {
        return new TemplateView(
            templateId: $template->templateId,
            ownerId: $template->ownerId,
            name: $template->name,
            engineType: $template->engineType,
            templateBody: $template->templateBody,
            isPublic: $template->isPublic,
            isActive: $template->isActive,
            createdAt: IsoDateTime::format($template->createdAt),
            updatedAt: IsoDateTime::format($template->updatedAt)
        );
    }
}
