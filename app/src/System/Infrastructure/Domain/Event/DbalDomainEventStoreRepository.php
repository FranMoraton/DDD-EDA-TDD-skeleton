<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Domain\Event;

use App\System\Domain\Event\DomainEvent;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

final class DbalDomainEventStoreRepository
{
    public const string EVENT_STORE = 'event_store';

    public function __construct(private readonly Connection $connection)
    {
    }

    public function add(DomainEvent $event): void
    {
        $stmt = $this->connection->prepare(
            \sprintf(
                'insert into %s
                (message_id, aggregate_id, aggregate_version, occurred_on, message_name, payload)
                values
                (:message_id, :aggregate_id, :aggregate_version, :occurred_on, :message_name, :payload)',
                self::EVENT_STORE,
            ),
        );

        $stmt->bindValue('message_id', $event->messageId()->value(), ParameterType::STRING);
        $stmt->bindValue('aggregate_id', $event->aggregateId()->value(), ParameterType::STRING);
        $stmt->bindValue('aggregate_version', $event->aggregateVersion(), ParameterType::INTEGER);
        $stmt->bindValue('occurred_on', $event->occurredOn(), ParameterType::STRING);
        $stmt->bindValue('message_name', $event::messageName(), ParameterType::STRING);
        $stmt->bindValue(
            'payload',
            \json_encode($event->payload(), \JSON_THROW_ON_ERROR, 512),
            ParameterType::STRING,
        );

        $stmt->executeQuery();
    }
}
