<?php

namespace App\Tests\Marketplace\Application\Command\EventProjections\Upsert;

use App\Marketplace\Application\Command\EventProjections\Upsert\UpsertEventCommandHandler;
use App\Marketplace\Domain\Model\EventProjection\EventProjection;
use App\Marketplace\Domain\Model\EventProjection\EventProjectionRepository;
use App\System\Domain\ValueObject\DateTimeValueObject;
use App\Tests\System\Infrastructure\Faker\FakerFactory;
use App\Tests\System\Infrastructure\PhpUnit\SpyTestHelper;
use PHPUnit\Framework\TestCase;

class UpsertEventProjectionCommandHandlerTest extends TestCase
{
    use SpyTestHelper;

    private EventProjectionRepository $eventProjectionRepository;

    public function testGivenUpsertCommandThenUpsertWithMinMaxPrize(): void
    {
        $faker = FakerFactory::create();

        $startDate = $faker->date('Y-m-d');
        $startDateTime = $faker->date('H:i:s');
        $endDate = $faker->date('Y-m-d');
        $endDateTime = $faker->date('H:i:s');

        $command = RandomUpsertEventProjectionCommand::execute(
            eventStartDate: DateTimeValueObject::from($startDate . ' ' . $startDateTime),
            eventEndDate: DateTimeValueObject::from($endDate . ' ' . $endDateTime),
            zones: [
                [
                    'zone_id' => $faker->randomNumber(),
                    'capacity' => $faker->randomNumber(),
                    'price' => 30.00,
                    'name' => $faker->name(),
                    'numbered' => $faker->boolean(),
                ],
                [
                    'zone_id' => $faker->randomNumber(),
                    'capacity' => $faker->randomNumber(),
                    'price' => $maxPrize = 31.5,
                    'name' => $faker->name(),
                    'numbered' => $faker->boolean(),
                ],
                [
                    'zone_id' => $faker->randomNumber(),
                    'capacity' => $faker->randomNumber(),
                    'price' => $minPrize = 28.5,
                    'name' => $faker->name(),
                    'numbered' => $faker->boolean(),
                ],
            ],
        );

        $this->eventProjectionRepository
            ->expects(self::once())
            ->method('upsertByEventDate')
            ->will(
                self::extractArguments($eventProjection),
            );

        ($this->handler)($command);

        self::assertEquals($eventProjection::modelName(), EventProjection::modelName());
        self::assertEquals($eventProjection->id(), $command->id());
        self::assertEquals($eventProjection->title(), $command->title());
        self::assertEquals($eventProjection->startDate(), $startDate);
        self::assertEquals($eventProjection->startTime(), $startDateTime);
        self::assertEquals($eventProjection->endDate(), $endDate);
        self::assertEquals($eventProjection->endTime(), $endDateTime);
        self::assertEquals($eventProjection->minPrice(), $minPrize);
        self::assertEquals($eventProjection->maxPrice(), $maxPrize);
        self::assertEquals($eventProjection->startsAt(), $command->eventStartDate());
        self::assertEquals($eventProjection->endsAt(), $command->eventEndDate());
        self::assertEquals($eventProjection->lastEventDate(), $command->lastEventDate());
    }

    public function testGivenUpsertCommandWithNoZonesThenUpsertWithMinMaxPrizeAs0(): void
    {
        $faker = FakerFactory::create();

        $startDate = $faker->date('Y-m-d');
        $startDateTime = $faker->date('H:i:s');
        $endDate = $faker->date('Y-m-d');
        $endDateTime = $faker->date('H:i:s');

        $command = RandomUpsertEventProjectionCommand::execute(
            eventStartDate: DateTimeValueObject::from($startDate . ' ' . $startDateTime),
            eventEndDate: DateTimeValueObject::from($endDate . ' ' . $endDateTime),
            zones: [],
        );

        $this->eventProjectionRepository
            ->expects(self::once())
            ->method('upsertByEventDate')
            ->will(
                self::extractArguments($eventProjection),
            );

        ($this->handler)($command);

        self::assertEquals($eventProjection::modelName(), EventProjection::modelName());
        self::assertEquals($eventProjection->id(), $command->id());
        self::assertEquals($eventProjection->title(), $command->title());
        self::assertEquals($eventProjection->startDate(), $startDate);
        self::assertEquals($eventProjection->startTime(), $startDateTime);
        self::assertEquals($eventProjection->endDate(), $endDate);
        self::assertEquals($eventProjection->endTime(), $endDateTime);
        self::assertEquals($eventProjection->minPrice(), 0.0);
        self::assertEquals($eventProjection->maxPrice(), 0.0);
        self::assertEquals($eventProjection->startsAt(), $command->eventStartDate());
        self::assertEquals($eventProjection->endsAt(), $command->eventEndDate());
        self::assertEquals($eventProjection->lastEventDate(), $command->lastEventDate());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventProjectionRepository = $this->createMock(EventProjectionRepository::class);

        $this->handler = new UpsertEventCommandHandler(
            $this->eventProjectionRepository,
        );
    }
}
