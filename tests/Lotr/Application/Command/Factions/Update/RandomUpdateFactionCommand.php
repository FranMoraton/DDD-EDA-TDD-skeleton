<?php

namespace App\Tests\Lotr\Application\Command\Factions\Update;

use App\Lotr\Application\Command\Factions\Update\UpdateFactionCommand;
use Faker\Factory;

class RandomUpdateFactionCommand
{
    public static function execute(
        ?string $id = null,
        ?string $name = null,
        ?string $description = null,
    ): UpdateFactionCommand {
        $faker = Factory::create();

        return new UpdateFactionCommand(
            $id ?? $faker->uuid(),
            $name ?? $faker->toUpper($faker->name()),
            $description ?? $faker->toUpper($faker->randomLetter()),
        );
    }
}
