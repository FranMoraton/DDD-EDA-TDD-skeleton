<?php

declare(strict_types=1);

namespace App\Marketplace\Infrastructure\Adapter\Amqp;

use App\Marketplace\Application\Command\EventProjections\Upsert\UpsertEventCommand;
use App\Marketplace\Domain\Model\Event\Event\EventWasUpdated;

final class EventWasUpdatedToUpsertEventProjection
{
    public function __invoke(EventWasUpdated $event): UpsertEventCommand
    {
        return UpsertEventCommand::create(
            $event->aggregateId()->value(),
            $event->baseEventId(),
            $event->sellMode(),
            $event->title(),
            $event->eventStartDate(),
            $event->eventEndDate(),
            $event->eventId(),
            $event->sellFrom(),
            $event->sellTo(),
            $event->soldOut(),
            $event->zones(),
            $event->occurredOn()->value(),
            $event->organizerCompanyId(),
            $event->minPrice(),
            $event->maxPrice(),
        );
    }
}
