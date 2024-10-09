<?php

namespace App\Tests\Lotr\Application\Command\Characters\Update;

use App\Lotr\Application\Command\Characters\Update\UpdateCharacterCommand;
use Faker\Factory;

class RandomUpdateCharacterCommand
{
    public static function execute(
        ?string $id = null,
        ?string $name = null,
        ?string $birthDate = null,
        ?string $kingdom = null,
        ?string $equipmentId = null,
        ?string $factionId = null,
    ): UpdateCharacterCommand {
        $faker = Factory::create();

        return new UpdateCharacterCommand(
            $id ?? $faker->uuid(),
            $name ?? $faker->name(),
            $birthDate ?? $faker->date(),
            $kingdom ?? $faker->toUpper($faker->randomLetter()),
            $equipmentId ?? $faker->uuid(),
            $factionId ?? $faker->uuid(),
        );
    }
}
