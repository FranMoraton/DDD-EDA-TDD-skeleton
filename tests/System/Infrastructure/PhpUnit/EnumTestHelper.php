<?php

declare(strict_types=1);

namespace App\Tests\System\Infrastructure\PhpUnit;

trait EnumTestHelper
{
    protected static function obtainRandomInstance(string $className): \UnitEnum
    {
        if (false === enum_exists($className)) {
            throw new \Exception("$className is not an enum");
        }

        $cases = $className::cases();

        if (0 === count($cases)) {
            throw new \Exception("$className have not defined cases");
        }

        $randomIndex = \random_int(0, count($cases) - 1);
        return $cases[$randomIndex];
    }

    protected static function obtainRandomValue(string $className): string
    {
        return self::obtainRandomInstance($className)->value;
    }
}
