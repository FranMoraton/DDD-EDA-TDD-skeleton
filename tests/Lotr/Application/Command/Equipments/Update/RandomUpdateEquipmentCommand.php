<?php

namespace App\Tests\Lotr\Application\Command\Equipments\Update;

use App\Lotr\Application\Command\Equipments\Update\UpdateEquipmentCommand;
use Faker\Factory;

class RandomUpdateEquipmentCommand
{
    public static function execute(
        ?string $id = null,
        ?string $name = null,
        ?string $type = null,
        ?string $madeBy = null,
    ): UpdateEquipmentCommand {
        $faker = Factory::create();

        return new UpdateEquipmentCommand(
            $id ?? $faker->uuid(),
            $name ?? $faker->toUpper($faker->name()),
            $type ?? $faker->toUpper($faker->randomLetter()),
            $madeBy ?? $faker->toUpper($faker->randomLetter()),
        );
    }
}
