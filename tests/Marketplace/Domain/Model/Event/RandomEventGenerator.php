<?php

declare(strict_types=1);

namespace App\Tests\Marketplace\Domain\Model\Event;

use App\Marketplace\Domain\Model\Event\Event;
use App\Tests\System\Infrastructure\Faker\FakerFactory;

final class RandomEventGenerator
{
    public static function execute(
        ?string $id = null,
    ): Event {
        $faker = FakerFactory::create();

        return Event::from(
            $id ?? $faker->uuid(),
        );
    }
}
