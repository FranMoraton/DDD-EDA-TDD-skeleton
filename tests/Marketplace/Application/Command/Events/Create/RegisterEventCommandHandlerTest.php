<?php

namespace App\Tests\Marketplace\Application\Command\Events\Create;

use App\Marketplace\Application\Command\Events\Register\RegisterEventCommandHandler;
use App\Marketplace\Domain\Model\Event\Event;
use App\Marketplace\Domain\Model\Event\Event\EventWasCreated;
use App\Marketplace\Domain\Model\Event\EventRepository;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\AlreadyExistException;
use App\Tests\Marketplace\Domain\Model\Event\RandomEventGenerator;
use App\Tests\System\Infrastructure\PhpUnit\SpyTestHelper;
use PHPUnit\Framework\TestCase;

class RegisterEventCommandHandlerTest extends TestCase
{
    use SpyTestHelper;

    private EventRepository $eventRepository;
    private DomainEventPublisher $domainEventPublisher;

    public function testGivenCreateWhenUserExistsThenFail(): void
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

    public function testGivenCreateCommandWhenUserDoesNotExistThenSuccess(): void
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
        $firstEvent = $event->events()[array_key_first($event->events())];
        self::assertEquals($firstEvent::messageName(), EventWasCreated::messageName());
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
