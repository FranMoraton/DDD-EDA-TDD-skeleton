<?php

declare(strict_types=1);

namespace App\Marketplace\Domain\Model\Event\Criteria;

use App\System\Domain\Criteria\Criteria;

final class EventsCriteria extends Criteria
{
    protected function allowedFields(): array
    {
        return [
            'base_event_id' => 'int'
        ];
    }
}
