<?php

declare(strict_types=1);

namespace domain\template\exception;

use domain\common\exception\DomainException;

class TemplateInactiveException extends DomainException
{
    public function __construct(string $templateId)
    {
        parent::__construct('template.exception.inactive: ' . $templateId, 4201);
    }
}

