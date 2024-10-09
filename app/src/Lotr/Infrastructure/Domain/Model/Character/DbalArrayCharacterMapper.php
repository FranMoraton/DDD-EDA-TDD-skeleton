<?php

namespace App\Lotr\Infrastructure\Domain\Model\Character;

use App\Lotr\Domain\Model\Character\Character;

class DbalArrayCharacterMapper
{
    public static function map(array $item): Character
    {
        return Character::from(
            $item['id'],
            $item['name'],
            $item['birth_date'],
            $item['kingdom'],
            $item['equipment_id'],
            $item['faction_id'],
        );
    }

    public static function toArray(Character $character): array
    {
        return [
            'id' => $character->id(),
            'name' => $character->name(),
            'birth_date' => $character->birthDate()->toDate(),
            'kingdom' => $character->kingdom(),
            'equipment_id' => $character->equipmentId(),
            'faction_id' => $character->factionId(),
        ];
    }
}
