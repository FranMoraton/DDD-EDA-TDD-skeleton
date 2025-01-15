<?php

declare(strict_types=1);

namespace App\System\Domain\Criteria;

enum Direction: string
{
    case ASC = 'ASC';
    case DESC = 'DESC';
}
