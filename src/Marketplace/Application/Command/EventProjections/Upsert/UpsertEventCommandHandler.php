<?php

namespace App\Marketplace\Application\Command\EventProjections\Upsert;

use App\Marketplace\Domain\Model\Event\Criteria\ByBaseEventIdCriteria;
use App\Marketplace\Domain\Model\Event\Event;
use App\Marketplace\Domain\Model\Event\EventRepository;
use App\Marketplace\Domain\Model\Event\ValueObject\Id;
use App\Marketplace\Domain\Model\Event\ValueObject\SellMode;
use App\Marketplace\Domain\Model\EventProjection\EventProjection;
use App\Marketplace\Domain\Model\EventProjection\EventProjectionRepository;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\AlreadyExistException;

final readonly class UpsertEventCommandHandler
{
    public function __construct(
        private EventProjectionRepository $eventProjectionRepository,
    ) {
    }

    public function __invoke(UpsertEventCommand $command): void
    {
        $startDateTime = $command->eventStartDate();
        $endDateTime = $command->eventEndDate();

        $eventProjection = new EventProjection(
            $command->id(),
            $command->title(),
            $startDateTime->format('Y-m-d'),
            $startDateTime->format('H:i:s'),
            $endDateTime->format('Y-m-d'),
            $endDateTime->format('H:i:s'),
            $command->minPrice(),
            $command->maxPrice(),
            $command->eventStartDate()->value(),
            $command->eventEndDate()->value(),
            $command->lastEventDate(),
        );

        $this->eventProjectionRepository->upsertByEventDate($eventProjection);
    }
}
