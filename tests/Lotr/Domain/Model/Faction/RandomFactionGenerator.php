<?php

declare(strict_types=1);

namespace App\Tests\Lotr\Domain\Model\Faction;

use App\Lotr\Domain\Model\Faction\Faction;
use Faker\Factory;

final class RandomFactionGenerator
{
    public static function execute(
        ?string $id = null,
        ?string $name = null,
        ?string $description = null,
    ): Faction {
        $faker = Factory::create();

        return Faction::from(
            $id ?? $faker->uuid(),
            $name ?? $faker->name,
            $description ?? $faker->text,
        );
    }
}
