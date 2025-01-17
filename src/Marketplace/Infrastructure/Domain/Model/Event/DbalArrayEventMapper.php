<?php

namespace App\Marketplace\Infrastructure\Domain\Model\Event;

use App\Marketplace\Domain\Model\Event\Event;

class DbalArrayEventMapper
{
    public static function map(array $item): Event
    {
        return Event::from(
            $item['id'],
        );
    }

    public static function toArray(Event $event): array
    {
        return [
            'id' => $event->id(),
        ];
    }
}
