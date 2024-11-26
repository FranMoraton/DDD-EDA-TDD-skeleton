<?php

namespace App\Tests\Lotr\Application\Command\Characters\Create;

use App\Lotr\Application\Command\Characters\Create\CreateCharacterCommand;
use Faker\Factory;

class RandomCreateCharacterCommand
{
    public static function execute(
        ?string $id = null,
        ?string $name = null,
        ?string $birthDate = null,
        ?string $kingdom = null,
        ?string $equipmentId = null,
        ?string $factionId = null,
    ): CreateCharacterCommand {
        $faker = Factory::create();

        return new CreateCharacterCommand(
            $id ?? $faker->uuid(),
            $name ?? $faker->name(),
            $birthDate ?? $faker->date(),
            $kingdom ?? $faker->toUpper($faker->randomLetter()),
            $equipmentId ?? $faker->uuid(),
            $factionId ?? $faker->uuid(),
        );
    }
}
