<?php

namespace App\Tests\Lotr\Application\Command\Factions\Create;

use App\Lotr\Application\Command\Factions\Create\CreateFactionCommand;
use Faker\Factory;

class RandomCreateFactionCommand
{
    public static function execute(
        ?string $id = null,
        ?string $name = null,
        ?string $description = null,
    ): CreateFactionCommand {
        $faker = Factory::create();

        return new CreateFactionCommand(
            $id ?? $faker->uuid(),
            $name ?? $faker->toUpper($faker->name()),
            $description ?? $faker->toUpper($faker->randomLetter()),
        );
    }
}
