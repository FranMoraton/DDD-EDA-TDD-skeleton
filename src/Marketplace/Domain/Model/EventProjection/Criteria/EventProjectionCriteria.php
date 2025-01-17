<?php

declare(strict_types=1);

namespace App\Marketplace\Domain\Model\EventProjection\Criteria;

use App\System\Domain\Criteria\Criteria;

final class EventProjectionCriteria extends Criteria
{
    protected function allowedFields(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
