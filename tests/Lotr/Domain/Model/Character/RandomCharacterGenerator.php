<?php

declare(strict_types=1);

namespace App\Tests\Lotr\Domain\Model\Character;

use App\Lotr\Domain\Model\Character\Character;
use Faker\Factory;

final class RandomCharacterGenerator
{
    public static function execute(
        ?string $id = null,
        ?string $name = null,
        ?string $birthDate = null,
        ?string $kingdom = null,
        ?string $equipmentId = null,
        ?string $factionId = null,
    ): Character {
        $faker = Factory::create();

        return Character::from(
            $id ?? $faker->uuid(),
            $name ?? $faker->name(),
            $birthDate ?? $faker->date(),
            $kingdom ?? $faker->text(),
            $equipmentId ?? $faker->uuid(),
            $factionId ?? $faker->uuid(),
        );
    }
}
