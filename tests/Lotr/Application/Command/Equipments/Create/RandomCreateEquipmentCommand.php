<?php

namespace App\Tests\Lotr\Application\Command\Equipments\Create;

use App\Lotr\Application\Command\Equipments\Create\CreateEquipmentCommand;
use Faker\Factory;

class RandomCreateEquipmentCommand
{
    public static function execute(
        ?string $id = null,
        ?string $name = null,
        ?string $type = null,
        ?string $madeBy = null,
    ): CreateEquipmentCommand {
        $faker = Factory::create();

        return new CreateEquipmentCommand(
            $id ?? $faker->uuid(),
            $name ?? $faker->toUpper($faker->name()),
            $type ?? $faker->toUpper($faker->randomLetter()),
            $madeBy ?? $faker->toUpper($faker->randomLetter()),
        );
    }
}
