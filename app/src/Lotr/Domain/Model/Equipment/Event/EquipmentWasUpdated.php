<?php

namespace App\Lotr\Domain\Model\Equipment\Event;

use App\Lotr\Domain\Model\Equipment\ValueObject\Id;
use App\Lotr\Domain\Model\Equipment\ValueObject\MadeBy;
use App\Lotr\Domain\Model\Equipment\ValueObject\Name;
use App\Lotr\Domain\Model\Equipment\ValueObject\Type;
use App\System\Domain\Event\DomainEvent;
use App\System\Domain\ValueObject\DateTimeValueObject;
use App\System\Domain\ValueObject\Uuid;

class EquipmentWasUpdated extends DomainEvent
{
    private const string NAME = 'company.app.1.domain_event.equipment.was_updated';

    private string $name;
    private string $type;
    private string $madeBy;

    public static function from(
        Id $aggregateId,
        Name $name,
        Type $type,
        MadeBy $madeBy,
    ): self {
        return self::fromPayload(
            Uuid::v4(),
            $aggregateId,
            DateTimeValueObject::now(),
            self::buildPayload(
                $name,
                $type,
                $madeBy,
            ),
        );
    }

    public function rebuildPayload(): void
    {
        $payload = $this->payload();
        $this->name = $payload['name'];
        $this->type = $payload['type'];
        $this->madeBy = $payload['made_by'];
    }

    private static function buildPayload(
        Name $name,
        Type $type,
        MadeBy $madeBy,
    ): array {
        return [
            'name' => $name->value(),
            'type' => $type->value(),
            'made_by' => $madeBy->value(),
        ];
    }

    public static function messageName(): string
    {
        return self::NAME;
    }
}
