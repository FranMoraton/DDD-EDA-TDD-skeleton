<?php

declare(strict_types=1);

namespace App\Tests\Marketplace\Domain\Model\Event;

use App\Marketplace\Domain\Model\Event\Event;
use App\Marketplace\Domain\Model\Event\ValueObject\SellMode;
use App\Marketplace\Domain\Model\Event\ValueObject\Zone;
use App\System\Infrastructure\Service\JsonSerializer;
use App\Tests\System\Infrastructure\Faker\FakerFactory;

final class RandomEventGenerator
{
    public static function execute(
        ?string $id = null,
        ?int $baseEventId = null,
        ?string $sellMode = null,
        ?string $title = null,
        ?string $eventStartDate = null,
        ?string $eventEndDate = null,
        ?int $eventId = null,
        ?string $sellFrom = null,
        ?string $sellTo = null,
        ?bool $soldOut = null,
        ?array $zones = null,
        ?string $requestTime = null,
        ?string $organizerCompanyId = null,
        ?float $minPrice = null,
        ?float $maxPrice = null,
    ): Event {
        $faker = FakerFactory::create();

        return Event::from(
            $id ?? $faker->uuid(),
            $baseEventId ?? $faker->randomNumber(),
            $sellMode ?? SellMode::ONLINE,
            $title ?? $faker->text(),
            $eventStartDate ?? $faker->dateTime()->format(\DATE_ATOM),
            $eventEndDate ?? $faker->dateTime()->format(\DATE_ATOM),
            $eventId ?? $faker->randomNumber(),
            $sellFrom ?? $faker->dateTime()->format(\DATE_ATOM),
            $sellTo ?? $faker->dateTime()->format(\DATE_ATOM),
            $soldOut ?? $faker->boolean(),
            $zones ?? [
                JsonSerializer::decodeArray(JsonSerializer::encode(
                    Zone::from(
                        $faker->randomNumber(),
                        $faker->randomNumber(),
                        $faker->randomFloat(),
                        $faker->text(),
                        $faker->boolean(),
                    ),
                )),
                ],
            $requestTime ?? $faker->dateTime()->format(\DATE_ATOM),
            $organizerCompanyId,
            $minPrice ?? $faker->randomNumber(),
            $maxPrice ?? $faker->randomNumber(),
        );
    }
}
