<?php

declare(strict_types=1);

namespace App\Marketplace\Application\Query\EventProjections\Search;

use App\Marketplace\Domain\Model\EventProjection\Criteria\BySearchCriteria;
use App\Marketplace\Domain\Model\EventProjection\EventProjectionRepository;

final readonly class SearchQueryHandler
{
    public function __construct(private EventProjectionRepository $eventProjectionRepository)
    {
    }

    public function __invoke(SearchQuery $query): array
    {
        $data = $this->eventProjectionRepository->search(
            BySearchCriteria::execute(
                $query->startsAt(),
                $query->endsAt(),
                $query->itemsPerPage(),
                $query->page(),
            ),
        );

        return $this->transformResponse($data);
    }

    private function transformResponse(array $data): array
    {
        return [
            'data' => ['events' => $data],
            'error' => null,
        ];
    }
}
