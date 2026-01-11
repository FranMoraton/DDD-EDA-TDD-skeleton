<?php

declare(strict_types=1);

namespace App\Marketplace\Domain\Model\EventProjection\Criteria;

use App\System\Domain\ValueObject\DateTimeValueObject;
use App\System\Domain\Criteria\FilterParser;

class BySearchCriteria
{
    private const int DEFAULT_PAGE = 1;
    private const int DEFAULT_ITEMS_PER_PAGE = 50;

    public static function execute(
        ?int $itemsPerPage = null,
        ?int $page = null,
        array $filters = [],
    ): EventProjectionCriteria {
        $criteria = new EventProjectionCriteria();

        FilterParser::applyFilters($criteria, $filters);

        $effectivePage = $page ?? self::DEFAULT_PAGE;
        $effectiveItemsPerPage = $itemsPerPage ?? self::DEFAULT_ITEMS_PER_PAGE;

        if (null !== $itemsPerPage && null !== $page) {
            $criteria->withOffset(
                $criteria->calculatePaginationOffSet(
                    $effectivePage,
                    $effectiveItemsPerPage,
                ),
            );
            $criteria->withLimit($effectiveItemsPerPage);
        }

        return $criteria;
    }
}
