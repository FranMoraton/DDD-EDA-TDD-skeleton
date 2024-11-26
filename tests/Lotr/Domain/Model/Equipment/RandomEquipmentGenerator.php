<?php

declare(strict_types=1);

namespace App\Tests\Lotr\Domain\Model\Equipment;

use App\Lotr\Domain\Model\Equipment\Equipment;
use Faker\Factory;

final class RandomEquipmentGenerator
{
    public static function execute(
        ?string $id = null,
        ?string $name = null,
        ?string $type = null,
        ?string $madeBy = null,
    ): Equipment {
        $faker = Factory::create();

        return Equipment::from(
            $id ?? $faker->uuid(),
            $name ?? $faker->name,
            $type ?? $faker->text,
            $madeBy ?? $faker->text,
        );
    }
}
