<?php

declare(strict_types=1);

namespace App\System\Domain\Exception;

enum ExceptionCode: int
{
    case NotFound = 404;
    case BadBusinessLogic = 409;
    case ValidationError = 400;

    public function toHttpCode(): int
    {
        return $this->value;
    }
}