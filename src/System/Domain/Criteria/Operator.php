<?php

declare(strict_types=1);

namespace App\System\Domain\Criteria;

enum Operator: string
{
    case EQUALS = '=';
    case NOT_EQUALS = '!=';
    case GREATER_THAN = '>';
    case LESS_THAN = '<';
    case GREATER_THAN_OR_EQUALS = '>=';
    case LESS_THAN_OR_EQUALS = '<=';
    case LIKE = 'LIKE';
}
