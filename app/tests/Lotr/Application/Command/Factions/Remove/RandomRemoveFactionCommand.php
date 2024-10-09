<?php

namespace App\Tests\Lotr\Application\Command\Factions\Remove;

use App\Lotr\Application\Command\Factions\Remove\RemoveFactionCommand;
use Faker\Factory;

class RandomRemoveFactionCommand
{
    public static function execute(
        ?string $id = null,
    ): RemoveFactionCommand {
        $faker = Factory::create();

        return new RemoveFactionCommand(
            $id ?? $faker->uuid(),
        );
    }
}
