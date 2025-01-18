<?php

declare(strict_types=1);

namespace App\Marketplace\Domain\Model\EventProjection\Criteria;

use App\System\Domain\Criteria\Operator;
use App\System\Domain\ValueObject\DateTimeValueObject;

final class BySearchCriteria
{
    private const int DEFAULT_PAGE = 1;
    private const int DEFAULT_ITEMS_PER_PAGE = 50;

    public static function execute(
        DateTimeValueObject $startsAt,
        DateTimeValueObject $endsAt,
        ?int $itemPerPage = null,
        ?int $page = null,
    ): EventProjectionCriteria {
        $criteria = new EventProjectionCriteria();

        $criteria->withFilter('starts_at', $startsAt, Operator::GREATER_THAN_OR_EQUALS);
        $criteria->withFilter('ends_at', $endsAt, Operator::LESS_THAN_OR_EQUALS);

        $criteria->withOffset(self::offset($page, $itemPerPage));
        $criteria->withLimit($itemPerPage);

        return $criteria;
    }

    private static function offset(?int $page, ?int $itemsPerPage): int
    {
        $page = $page ?? self::DEFAULT_PAGE;
        $itemsPerPage = $itemsPerPage ?? self::DEFAULT_ITEMS_PER_PAGE;

        \assert($page > 0);
        \assert($itemsPerPage > 0);

        return ($page - 1) * $itemsPerPage;
    }
}
