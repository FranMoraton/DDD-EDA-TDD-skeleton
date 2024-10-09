<?php

namespace App\Tests\Lotr\Application\Command\Equipments\Remove;

use App\Lotr\Application\Command\Equipments\Remove\RemoveEquipmentCommand;
use Faker\Factory;

class RandomRemoveEquipmentCommand
{
    public static function execute(
        ?string $id = null,
    ): RemoveEquipmentCommand {
        $faker = Factory::create();

        return new RemoveEquipmentCommand(
            $id ?? $faker->uuid(),
        );
    }
}
