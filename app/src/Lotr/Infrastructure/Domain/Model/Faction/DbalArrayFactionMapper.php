<?php

namespace App\Lotr\Infrastructure\Domain\Model\Faction;

use App\Lotr\Domain\Model\Faction\Faction;

class DbalArrayFactionMapper
{
    public static function map(array $item): Faction
    {
        return Faction::from(
            $item['id'],
            $item['faction_name'],
            $item['description'],
        );
    }

    public static function toArray(Faction $faction): array
    {
        return [
            'id' => $faction->id(),
            'faction_name' => $faction->name(),
            'description' => $faction->description(),
        ];
    }
}