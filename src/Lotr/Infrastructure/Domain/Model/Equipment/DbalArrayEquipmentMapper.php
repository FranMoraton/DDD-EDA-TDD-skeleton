<?php

namespace App\Lotr\Infrastructure\Domain\Model\Equipment;

use App\Lotr\Domain\Model\Equipment\Equipment;

class DbalArrayEquipmentMapper
{
    public static function map(array $item): Equipment
    {
        return Equipment::from(
            $item['id'],
            $item['name'],
            $item['type'],
            $item['made_by'],
        );
    }

    public static function toArray(Equipment $equipment): array
    {
        return [
            'id' => $equipment->id(),
            'name' => $equipment->name(),
            'type' => $equipment->type(),
            'made_by' => $equipment->madeBy(),
        ];
    }
}
