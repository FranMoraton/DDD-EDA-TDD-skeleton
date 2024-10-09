<?php

declare(strict_types=1);

namespace App\Lotr\Domain\Model\Equipment;

use App\Lotr\Domain\Model\Equipment\ValueObject\Id;

interface EquipmentRepository
{
    public function byId(Id $id): ?Equipment;
    public function add(Equipment $equipment): void;
    public function update(Equipment $equipment): void;
    public function remove(Equipment $equipment): void;
}
