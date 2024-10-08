<?php

namespace App\Lotr\Domain\Model\Faction\Event;

use App\Lotr\Domain\Model\Faction\ValueObject\Description;
use App\Lotr\Domain\Model\Faction\ValueObject\Id;
use App\Lotr\Domain\Model\Faction\ValueObject\Name;
use App\System\Domain\Event\DomainEvent;
use App\System\Domain\ValueObject\DateTimeValueObject;
use App\System\Domain\ValueObject\Uuid;

class FactionWasCreated extends DomainEvent
{
    private const string NAME = 'company.app.1.domain_event.faction.was_created';

    private string $name;
    private string $description;

    public static function from(
        Id $aggregateId,
        Name $name,
        Description $description,
    ): self {
        return self::fromPayload(
            Uuid::v4(),
            $aggregateId,
            DateTimeValueObject::now(),
            self::buildPayload(
                $name,
                $description,
            ),
        );
    }

    public function rebuildPayload(): void
    {
        $payload = $this->payload();
        $this->name = $payload['name'];
        $this->description = $payload['description'];
    }

    private static function buildPayload(
        Name $name,
        Description $description,
    ): array {
        return [
            'name' => $name->value(),
            'description' => $description->value(),
        ];
    }

    public static function messageName(): string
    {
        return self::NAME;
    }
}
