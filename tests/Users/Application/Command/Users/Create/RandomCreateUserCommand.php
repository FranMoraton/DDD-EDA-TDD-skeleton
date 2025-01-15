<?php

namespace App\Tests\Users\Application\Command\Users\Create;

use App\Tests\System\Infrastructure\Faker\FakerFactory;
use App\Tests\System\Infrastructure\PhpUnit\EnumTestHelper;
use App\Users\Application\Command\Users\Create\CreateUserCommand;
use App\Users\Domain\Model\User\ValueObject\Role;

class RandomCreateUserCommand
{
    use EnumTestHelper;

    public static function execute(
        ?string $id = null,
        ?string $email = null,
        ?string $password = null,
        ?string $role = null,
    ): CreateUserCommand {
        $faker = FakerFactory::create();

        return CreateUserCommand::create(
            $id ?? $faker->uuid(),
            $email ?? $faker->email(),
            $role ?? self::obtainRandomValue(Role::class),
            $password ?? $faker->validPassword(),
        );
    }
}
