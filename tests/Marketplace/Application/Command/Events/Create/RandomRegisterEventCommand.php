<?php

namespace App\Tests\Marketplace\Application\Command\Events\Create;

use App\Marketplace\Application\Command\Events\Register\RegisterEventCommand;
use App\Tests\System\Infrastructure\Faker\FakerFactory;
use App\Tests\System\Infrastructure\PhpUnit\EnumTestHelper;

class RandomRegisterEventCommand
{
    use EnumTestHelper;

    public static function execute(
        ?string $id = null,
    ): RegisterEventCommand {
        $faker = FakerFactory::create();

        return RegisterEventCommand::create(
            $id ?? $faker->uuid(),
        );
    }
}
