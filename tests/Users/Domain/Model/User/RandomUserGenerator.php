<?php

declare(strict_types=1);

namespace App\Tests\Users\Domain\Model\User;

use App\Tests\System\Infrastructure\Faker\FakerFactory;
use App\Tests\System\Infrastructure\PhpUnit\EnumTestHelper;
use App\Users\Domain\Model\User\User;
use App\Users\Domain\Model\User\ValueObject\Role;

final class RandomUserGenerator
{
    use EnumTestHelper;

    public static function execute(
        ?string $id = null,
        ?string $email = null,
        ?string $password = null,
        ?string $role = null,
    ): User {
        $faker = FakerFactory::create();

        return User::from(
            $id ?? $faker->uuid(),
            $email ?? $faker->email(),
            $password ?? $faker->validPassword(),
            $role ?? self::obtainRandomValue(Role::class),
        );
    }
}
