<?php

namespace App\Marketplace\Infrastructure\Domain\Model\EventProjection;

use App\Marketplace\Domain\Model\EventProjection\EventProjection;

class DbalArrayEventProjectionMapper
{
    public static function map(array $item): EventProjection
    {
        return new EventProjection(
            $item['id'],
            $item['title'],
            $item['start_date'],
            $item['start_time'],
            $item['end_date'],
            $item['end_time'],
            $item['min_price'],
            $item['max_price'],
            $item['starts_at'],
            $item['ends_at'],
            $item['last_event_date']
        );
    }

    public static function toArray(EventProjection $event): array
    {
        return [
            'id' => $event->id(),
            'title' => $event->title(),
            'start_date' => $event->startDate(),
            'start_time' => $event->startTime(),
            'end_date' => $event->endDate(),
            'end_time' => $event->endTime(),
            'min_price' => $event->minPrice(),
            'max_price' => $event->maxPrice(),
            'starts_at' => $event->startsAt(),
            'ends_at' => $event->endsAt(),
            'last_event_date' => $event->lastEventDate(),
        ];
    }
}
