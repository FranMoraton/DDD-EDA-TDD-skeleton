<?php

declare(strict_types=1);

namespace App\Marketplace\Domain\Model\EventProjection\Criteria;

use App\System\Domain\Criteria\Operator;
use App\System\Domain\ValueObject\DateTimeValueObject;

final class BySearchCriteria
{
    public static function execute(DateTimeValueObject $startsAt, DateTimeValueObject $endsAt): EventProjectionCriteria
    {
        $criteria = new EventProjectionCriteria();

        $criteria->withFilter('starts_at', $startsAt, Operator::GREATER_THAN_OR_EQUALS);
        $criteria->withFilter('ends_at', $endsAt, Operator::LESS_THAN_OR_EQUALS);

        return $criteria;
    }
}
