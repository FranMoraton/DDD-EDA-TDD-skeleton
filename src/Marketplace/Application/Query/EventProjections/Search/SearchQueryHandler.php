<?php

declare(strict_types=1);

namespace App\Marketplace\Application\Query\EventProjections\Search;

use App\Marketplace\Domain\Model\EventProjection\Criteria\BySearchCriteria;
use App\Marketplace\Domain\Model\EventProjection\EventProjectionRepository;
use App\System\Application\Query\SearchResponse;

final readonly class SearchQueryHandler
{
    public function __construct(private EventProjectionRepository $eventProjectionRepository)
    {
    }

    public function __invoke(SearchQuery $query): SearchResponse
    {
        $paginatedCriteria = BySearchCriteria::execute(
            $query->itemsPerPage(),
            $query->page(),
            $query->filters(),
        );

        $countCriteria = BySearchCriteria::execute(
            null,
            null,
            $query->filters(),
        );

        $items = $this->eventProjectionRepository->search($paginatedCriteria);
        $total = $this->eventProjectionRepository->count($countCriteria);

        return SearchResponse::create($items, $total, $query->page(), $query->itemsPerPage());
    }
}
