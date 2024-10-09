<?php

namespace App\Tests\Lotr\Application\Command\Characters\Remove;

use App\Lotr\Application\Command\Characters\Remove\RemoveCharacterCommand;
use Faker\Factory;

class RandomRemoveCharacterCommand
{
    public static function execute(
        ?string $id = null,
    ): RemoveCharacterCommand {
        $faker = Factory::create();

        return new RemoveCharacterCommand(
            $id ?? $faker->uuid(),
        );
    }
}
