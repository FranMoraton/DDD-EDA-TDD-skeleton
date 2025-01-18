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
    private UpsertEventCommandHandler $handler;

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
        self::assertEquals($eventProjection->minPrice(), $command->minPrice());
        self::assertEquals($eventProjection->maxPrice(), $command->maxPrice());
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
