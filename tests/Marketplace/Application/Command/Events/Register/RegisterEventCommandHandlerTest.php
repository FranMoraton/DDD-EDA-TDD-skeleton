<?php

namespace App\Tests\Marketplace\Application\Command\Events\Register;

use App\Marketplace\Application\Command\Events\Register\RegisterEventCommandHandler;
use App\Marketplace\Domain\Model\Event\Event;
use App\Marketplace\Domain\Model\Event\Event\EventWasCreated;
use App\Marketplace\Domain\Model\Event\Event\EventWasUpdated;
use App\Marketplace\Domain\Model\Event\EventRepository;
use App\Marketplace\Domain\Model\Event\ValueObject\SellMode;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\AlreadyExistException;
use App\System\Domain\Service\JsonSerializer;
use App\System\Domain\ValueObject\DateTimeValueObject;
use App\Tests\Marketplace\Domain\Model\Event\RandomEventGenerator;
use App\Tests\System\Infrastructure\PhpUnit\SpyTestHelper;
use PHPUnit\Framework\TestCase;

class RegisterEventCommandHandlerTest extends TestCase
{
    use SpyTestHelper;

    private EventRepository $eventRepository;
    private DomainEventPublisher $domainEventPublisher;

    private RegisterEventCommandHandler $handler;

    public function testGivenCreateWhenEventExistsThenFail(): void
    {
        $command = RandomRegisterEventCommand::execute();

        $this->eventRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(
                RandomEventGenerator::execute(),
            );

        $this->eventRepository
            ->expects(self::never())
            ->method('add');

        $this->domainEventPublisher
            ->expects(self::never())
            ->method('execute');

        self::expectException(AlreadyExistException::class);
        ($this->handler)($command);
    }

    public function testGivenCreateWhenEventExistsThenDoNothing(): void
    {
        $command = RandomRegisterEventCommand::execute(sellMode: SellMode::OFFLINE);

        $this->eventRepository
            ->expects(self::never())
            ->method('byId');

        $this->eventRepository
            ->expects(self::never())
            ->method('add');

        $this->domainEventPublisher
            ->expects(self::never())
            ->method('execute');

        ($this->handler)($command);
    }


    public function testGivenCreateCommandWhenEventDoesNotExistThenSuccess(): void
    {
        $command = RandomRegisterEventCommand::execute();

        $this->eventRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(null);

        $this->eventRepository
            ->expects(self::once())
            ->method('add');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($event),
            );

        ($this->handler)($command);

        self::assertEquals($event::modelName(), Event::modelName());
        self::assertEquals($event->id(), $command->id());
        self::assertEquals($command->id(), $event->id()->value());
        self::assertEquals($command->baseEventId(), $event->baseEventId());
        self::assertEquals($command->sellMode(), $event->sellMode()->value());
        self::assertEquals($command->title(), $event->title()->value());
        self::assertEquals($command->eventStartDate(), $event->eventStartDate());
        self::assertEquals($command->eventEndDate(), $event->eventEndDate());
        self::assertEquals($command->eventId(), $event->eventId());
        self::assertEquals($command->sellFrom(), $event->sellFrom());
        self::assertEquals($command->sellTo(), $event->sellTo());
        self::assertEquals($command->soldOut(), $event->soldOut());
        self::assertEquals($command->zones(), $event->zones()->toArray());
        self::assertEquals($command->requestTime(), $event->requestTime());
        self::assertEquals($command->organizerCompanyId(), $event->organizerCompanyId()?->value());

        $firstEvent = $event->events()[array_key_first($event->events())];
        self::assertEquals($firstEvent::messageName(), EventWasCreated::messageName());
        self::assertEquals($event->id(), $firstEvent->aggregateId()->value());
        self::assertEquals($event->baseEventId(), $firstEvent->baseEventId());
        self::assertEquals($event->sellMode(), $firstEvent->sellMode());
        self::assertEquals($event->title(), $firstEvent->title());
        self::assertEquals($event->eventStartDate()->value(), $firstEvent->eventStartDate());
        self::assertEquals($event->eventEndDate()->value(), $firstEvent->eventEndDate());
        self::assertEquals($event->eventId(), $firstEvent->eventId());
        self::assertEquals($event->sellFrom()->value(), $firstEvent->sellFrom());
        self::assertEquals($event->sellTo()->value(), $firstEvent->sellTo());
        self::assertEquals($event->soldOut(), $firstEvent->soldOut());
        self::assertEquals($event->zones()->toArray(), $firstEvent->zones());
        self::assertEquals($event->requestTime()->value(), $firstEvent->requestTime());
        self::assertEquals($event->organizerCompanyId(), $firstEvent->organizerCompanyId());
    }

    public function testGivenRegisterCommandWhenEventBaseExistButIsNewerExistThenDoesNotUpdate(): void
    {
        $command = RandomRegisterEventCommand::execute(requestTime: DateTimeValueObject::from('now'));

        $this->eventRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(null);

        $this->eventRepository
            ->expects(self::once())
            ->method('search')
            ->willReturn([RandomEventGenerator::execute(requestTime: DateTimeValueObject::from('now +2 days'))]);

        $this->eventRepository
            ->expects(self::once())
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($event),
            );

        ($this->handler)($command);

        self::assertEquals($event::modelName(), Event::modelName());
        self::assertCount(0, $event->events());
    }

    public function testGivenRegisterCommandWhenEventBaseExistAndIsOlderButDataIsEqualThenDoNotUpdate(): void
    {
        $command = RandomRegisterEventCommand::execute(requestTime: DateTimeValueObject::from('now +2 days'));

        $this->eventRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(null);

        $this->eventRepository
            ->expects(self::once())
            ->method('search')
            ->willReturn(
                [
                    $initialEvent = RandomEventGenerator::execute(
                        $command->id(),
                        $command->baseEventId(),
                        $command->sellMode(),
                        $command->title(),
                        $command->eventStartDate(),
                        $command->eventEndDate(),
                        $command->eventId(),
                        $command->sellFrom(),
                        $command->sellTo(),
                        $command->soldOut(),
                        $command->zones(),
                        DateTimeValueObject::from('now'),
                        $command->organizerCompanyId(),
                    ),
                ],
            );

        $this->eventRepository
            ->expects(self::once())
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($event),
            );

        ($this->handler)($command);

        self::assertEquals($event::modelName(), Event::modelName());
        self::assertCount(0, $event->events());
        self::assertEquals(JsonSerializer::encode($event), JsonSerializer::encode($initialEvent));
    }

    public function testGivenRegisterCommandWhenEventBaseExistAndIsOlderThenUpdate(): void
    {
        $command = RandomRegisterEventCommand::execute(requestTime: DateTimeValueObject::from('now +2 days'));

        $this->eventRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(null);

        $this->eventRepository
            ->expects(self::once())
            ->method('search')
            ->willReturn([RandomEventGenerator::execute(requestTime: DateTimeValueObject::from('now'))]);

        $this->eventRepository
            ->expects(self::once())
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($event),
            );

        ($this->handler)($command);

        self::assertEquals($event::modelName(), Event::modelName());
        self::assertCount(1, $event->events());
        $firstEvent = $event->events()[array_key_first($event->events())];
        self::assertEquals($firstEvent::messageName(), EventWasUpdated::messageName());
        self::assertEquals($event->id(), $firstEvent->aggregateId()->value());
        self::assertEquals($event->baseEventId(), $firstEvent->baseEventId());
        self::assertEquals($event->sellMode(), $firstEvent->sellMode());
        self::assertEquals($event->title(), $firstEvent->title());
        self::assertEquals($event->eventStartDate()->value(), $firstEvent->eventStartDate());
        self::assertEquals($event->eventEndDate()->value(), $firstEvent->eventEndDate());
        self::assertEquals($event->eventId(), $firstEvent->eventId());
        self::assertEquals($event->sellFrom()->value(), $firstEvent->sellFrom());
        self::assertEquals($event->sellTo()->value(), $firstEvent->sellTo());
        self::assertEquals($event->soldOut(), $firstEvent->soldOut());
        self::assertEquals($event->zones()->toArray(), $firstEvent->zones());
        self::assertEquals($event->requestTime()->value(), $firstEvent->requestTime());
        self::assertEquals($event->organizerCompanyId(), $firstEvent->organizerCompanyId());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventRepository = $this->createMock(EventRepository::class);
        $this->domainEventPublisher = $this->createMock(DomainEventPublisher::class);

        $this->handler = new RegisterEventCommandHandler(
            $this->eventRepository,
            $this->domainEventPublisher,
        );
    }
}
