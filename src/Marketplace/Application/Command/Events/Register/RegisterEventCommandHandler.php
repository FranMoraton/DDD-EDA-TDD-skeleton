<?php

namespace App\Marketplace\Application\Command\Events\Register;

use App\Marketplace\Domain\Model\Event\Event;
use App\Marketplace\Domain\Model\Event\EventRepository;
use App\Marketplace\Domain\Model\Event\ValueObject\Id;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\AlreadyExistException;

final readonly class RegisterEventCommandHandler
{
    public function __construct(
        private EventRepository $eventRepository,
        private DomainEventPublisher $domainEventPublisher,
    ) {
    }

    public function __invoke(RegisterEventCommand $command): void
    {
        $this->assertThatEventDoesNotExist($command);

        $event = Event::create(
            $command->id(),
        );

        $this->eventRepository->add($event);

        $this->domainEventPublisher->execute($event);
    }

    public function assertThatEventDoesNotExist(RegisterEventCommand $command): void
    {
        $character = $this->eventRepository->byId(Id::from($command->id()));

        if (null !== $character) {
            throw new AlreadyExistException(Event::modelName(), Event::modelName(), ['id' => $command->id()]);
        }
    }
}
