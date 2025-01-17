<?php

declare(strict_types=1);

namespace App\Marketplace\Application\Query\Events\ById;

use App\Marketplace\Domain\Model\Event\Event;
use App\Marketplace\Domain\Model\Event\EventRepository;
use App\Marketplace\Domain\Model\Event\ValueObject\Id;

final readonly class GetByIdQueryHandler
{
    public function __construct(private EventRepository $eventRepository)
    {
    }

    public function __invoke(GetByIdQuery $query): ?Event
    {
        return $this->eventRepository->byId(Id::from($query->id()));
    }
}
