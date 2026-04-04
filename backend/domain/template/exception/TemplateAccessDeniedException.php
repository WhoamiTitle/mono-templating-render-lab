<?php

declare(strict_types=1);

namespace domain\template\exception;

use domain\common\exception\AccessDeniedException;

class TemplateAccessDeniedException extends AccessDeniedException
{
    public function __construct(string $templateId, string $ownerId, string $actorId)
    {
        parent::__construct(
            'template.exception.access_denied: template=' . $templateId
            . ', owner=' . $ownerId
            . ', actor=' . $actorId,
            4205
        );
    }
}

