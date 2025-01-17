<?php

declare(strict_types=1);

namespace App\Tests\Marketplace\Domain\Model\EventProjection;

use App\Marketplace\Domain\Model\EventProjection\EventProjection;
use App\Tests\System\Infrastructure\Faker\FakerFactory;

final class RandomEventProjectionGenerator
{
    public static function execute(
        ?string $id = null,
        ?string $title = null,
        ?string $startDate = null,
        ?string $startTime = null,
        ?string $endDate = null,
        ?string $endTime = null,
        ?float $minPrice = null,
        ?float $maxPrice = null,
        ?string $startsAt = null,
        ?string $endsAt = null,
        ?string $lastEventDate = null,
    ): EventProjection {
        $faker = FakerFactory::create();

        return new EventProjection(
            $id ?? $faker->uuid(),
            $title ?? $faker->text,
            $startDate ?? $faker->date('Y-m-d'),
            $startTime ?? $faker->date('H:i:s'),
            $endDate ?? $faker->date('Y-m-d'),
            $endTime ?? $faker->date('H:i:s'),
            $minPrice ?? $faker->randomFloat(),
            $maxPrice ?? $faker->randomFloat(),
            $startsAt ?? $faker->dateTime()->format(\DATE_ATOM),
            $endsAt ?? $faker->dateTime()->format(\DATE_ATOM),
            $lastEventDate ?? $faker->dateTime()->format(\DATE_ATOM),
        );
    }
}
