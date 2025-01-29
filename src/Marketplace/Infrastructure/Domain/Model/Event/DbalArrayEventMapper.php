<?php

namespace App\Marketplace\Infrastructure\Domain\Model\Event;

use App\Marketplace\Domain\Model\Event\Event;
use App\System\Domain\Service\JsonSerializer;

class DbalArrayEventMapper
{
    public static function map(array $item): Event
    {
        return Event::from(
            $item['id'],
            $item['base_event_id'],
            $item['sell_mode'],
            $item['title'],
            $item['event_start_date'],
            $item['event_end_date'],
            $item['event_id'],
            $item['sell_from'],
            $item['sell_to'],
            $item['sold_out'],
            JsonSerializer::decodeArray($item['zones']),
            $item['request_time'],
            $item['organizer_company_id'],
            $item['min_price'],
            $item['max_price'],
        );
    }

    public static function toArray(Event $event): array
    {
        return [
            'id' => $event->id()->value(),
            'base_event_id' => $event->baseEventId(),
            'sell_mode' => $event->sellMode()->value(),
            'title' => $event->title()->value(),
            'event_start_date' => $event->eventStartDate()->value(),
            'event_end_date' => $event->eventEndDate()->value(),
            'event_id' => $event->eventId(),
            'sell_from' => $event->sellFrom()->value(),
            'sell_to' => $event->sellTo()->value(),
            'sold_out' => \var_export($event->soldOut(), true),
            'zones' => JsonSerializer::encode($event->zones()),
            'request_time' => $event->requestTime()->value(),
            'organizer_company_id' => $event->organizerCompanyId()?->value(),
            'min_price' => $event->minPrice(),
            'max_price' => $event->maxPrice(),
        ];
    }
}
