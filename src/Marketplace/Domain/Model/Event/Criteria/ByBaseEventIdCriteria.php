<?php

declare(strict_types=1);

namespace App\Marketplace\Domain\Model\Event\Criteria;

use App\System\Domain\Criteria\Operator;
use App\Users\Domain\Model\User\Criteria\UsersCriteria;
use App\Users\Domain\Model\User\ValueObject\Email;

final class ByBaseEventIdCriteria
{
    public static function create(int $baseEventId): EventsCriteria
    {
        $criteria = new EventsCriteria();
        $criteria->withFilter('base_event_id', $baseEventId, Operator::EQUALS);
        $criteria->withLimit(1);

        return $criteria;
    }
}
