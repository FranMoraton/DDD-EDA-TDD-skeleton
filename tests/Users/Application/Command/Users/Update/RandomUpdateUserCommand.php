<?php

namespace App\Tests\Users\Application\Command\Users\Update;

use App\Tests\System\Infrastructure\Faker\FakerFactory;
use App\Tests\System\Infrastructure\PhpUnit\EnumTestHelper;
use App\Users\Application\Command\Users\Update\UpdateUserCommand;
use App\Users\Domain\Model\User\ValueObject\Role;

class RandomUpdateUserCommand
{
    use EnumTestHelper;

    public static function execute(
        ?string $id = null,
        ?string $email = null,
        ?string $role = null,
    ): UpdateUserCommand {
        $faker = FakerFactory::create();

        return UpdateUserCommand::create(
            $id ?? $faker->uuid(),
            $email ?? $faker->email(),
            $role ?? self::obtainRandomValue(Role::class),
        );
    }
}
