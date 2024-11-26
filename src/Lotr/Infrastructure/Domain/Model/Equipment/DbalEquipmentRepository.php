<?php

declare(strict_types=1);

namespace App\Lotr\Infrastructure\Domain\Model\Equipment;

use App\Lotr\Domain\Model\Equipment\Equipment;
use App\Lotr\Domain\Model\Equipment\EquipmentRepository;
use App\Lotr\Domain\Model\Equipment\ValueObject\Id;
use App\System\Infrastructure\Dbal\DbalRepository;

class DbalEquipmentRepository extends DbalRepository implements EquipmentRepository
{
    private const string TABLE_NAME = 'equipments';

    public function byId(Id $id): ?Equipment
    {
        $result = $this->findOnyByIdentification(
            self::TABLE_NAME,
            $id->value(),
            'id'
        );

        return null !== $result
            ? $this->map($result)
            : null;
    }

    public function add(Equipment $equipment): void
    {
        $this->executeInsert(DbalArrayEquipmentMapper::toArray($equipment));
    }

    public function update(Equipment $equipment): void
    {
        $this->executeUpdate(
            $equipment,
            DbalArrayEquipmentMapper::toArray($equipment),
            [
                'id' => $equipment->id(),
            ],
        );
    }

    public function remove(Equipment $equipment): void
    {
        $this->executeDelete(['id' => $equipment->id()]);
    }

    protected static function tableName(): string
    {
        return self::TABLE_NAME;
    }

    protected function map(array $item): Equipment
    {
        $model = $this->addControlFields($item, DbalArrayEquipmentMapper::map($item));
        \assert($model instanceof Equipment);

        return $model;
    }
}
