<?php

namespace App\Lotr\Domain\Model\Character\Event;

use App\Lotr\Domain\Model\Character\ValueObject\BirthDate;
use App\Lotr\Domain\Model\Character\ValueObject\EquipmentId;
use App\Lotr\Domain\Model\Character\ValueObject\FactionId;
use App\Lotr\Domain\Model\Character\ValueObject\Id;
use App\Lotr\Domain\Model\Character\ValueObject\Kingdom;
use App\Lotr\Domain\Model\Character\ValueObject\Name;
use App\System\Domain\Event\DomainEvent;
use App\System\Domain\ValueObject\DateTimeValueObject;
use App\System\Domain\ValueObject\Uuid;

class CharacterWasCreated extends DomainEvent
{
    private const string NAME = 'company.app.1.domain_event.character.was_created';

    private string $name;
    private string $birthDate;
    private string $kingdom;
    private string $equipmentId;
    private string $factionId;

    public static function from(
        Id $aggregateId,
        Name $name,
        BirthDate $birthDate,
        Kingdom $kingdom,
        EquipmentId $equipmentId,
        FactionId $factionId,
    ): self {
        return self::fromPayload(
            Uuid::v4(),
            $aggregateId,
            DateTimeValueObject::now(),
            self::buildPayload(
                $name,
                $birthDate,
                $kingdom,
                $equipmentId,
                $factionId,
            ),
        );
    }

    public function rebuildPayload(): void
    {
        $payload = $this->payload();
        $this->name = $payload['name'];
        $this->birthDate = $payload['birth_date'];
        $this->kingdom = $payload['kingdom'];
        $this->equipmentId = $payload['equipment_id'];
        $this->factionId = $payload['faction_id'];
    }

    private static function buildPayload(
        Name $name,
        BirthDate $birthDate,
        Kingdom $kingdom,
        EquipmentId $equipmentId,
        FactionId $factionId,
    ): array {
        return [
            'name' => $name->value(),
            'birth_date' => $birthDate->value(),
            'kingdom' => $kingdom->value(),
            'equipment_id' => $equipmentId->value(),
            'faction_id' => $factionId->value(),
        ];
    }

    public static function messageName(): string
    {
        return self::NAME;
    }
}
