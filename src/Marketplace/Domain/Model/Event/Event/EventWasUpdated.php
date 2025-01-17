<?php

namespace App\Marketplace\Domain\Model\Event\Event;

use App\Marketplace\Domain\Model\Event\ValueObject\Id;
use App\System\Domain\Event\DomainEvent;
use App\System\Domain\ValueObject\DateTimeValueObject;
use App\System\Domain\ValueObject\Uuid;

class EventWasUpdated extends DomainEvent
{
    private const string NAME = 'company.marketplace.1.domain_event.event.was_updated';

    public static function from(
        Id $aggregateId,
    ): self {
        return self::fromPayload(
            Uuid::v4(),
            $aggregateId,
            DateTimeValueObject::now(),
            [],
        );
    }

    public function rebuildPayload(): void
    {
        $payload = $this->payload();
    }

    public static function messageName(): string
    {
        return self::NAME;
    }
}
