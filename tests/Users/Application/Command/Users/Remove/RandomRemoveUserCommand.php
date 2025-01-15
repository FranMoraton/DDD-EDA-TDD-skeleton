<?php

namespace App\Tests\Users\Application\Command\Users\Remove;

use App\Users\Application\Command\Users\Remove\RemoveUserCommand;
use Faker\Factory;

class RandomRemoveUserCommand
{
    public static function execute(
        ?string $id = null,
    ): RemoveUserCommand {
        $faker = Factory::create();

        return RemoveUserCommand::create(
            $id ?? $faker->uuid(),
        );
    }
}
