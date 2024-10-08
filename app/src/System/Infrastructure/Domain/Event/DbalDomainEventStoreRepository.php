<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Domain\Event;

use App\System\Domain\Event\DomainEvent;
use App\System\Domain\Event\DomainEventStoreRepository;
use Doctrine\DBAL\Connection;

final class DbalDomainEventStoreRepository implements DomainEventStoreRepository
{
    public const string EVENT_STORE = 'event_store';

    public function __construct(private readonly Connection $connection)
    {
    }

    public function add(DomainEvent $event): void
    {
        $this->connection->insert(
            self::EVENT_STORE,
            [
                'message_id' => $event->messageId()->value(),
                'aggregate_id' => $event->aggregateId()->value(),
                'aggregate_version' => (int) $event->aggregateVersion(),
                'occurred_on' => $event->occurredOn()->format('U.u'),
                'message_name' => $event::messageName(),
                'payload' => \json_encode($event->payload(), \JSON_THROW_ON_ERROR, 512)
            ]
        );
    }
}
