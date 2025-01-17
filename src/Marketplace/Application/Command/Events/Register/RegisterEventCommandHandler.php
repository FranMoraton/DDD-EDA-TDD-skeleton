<?php

namespace App\Marketplace\Application\Command\Events\Register;

use App\Marketplace\Domain\Model\Event\Criteria\ByBaseEventIdCriteria;
use App\Marketplace\Domain\Model\Event\Event;
use App\Marketplace\Domain\Model\Event\EventRepository;
use App\Marketplace\Domain\Model\Event\ValueObject\Id;
use App\Marketplace\Domain\Model\Event\ValueObject\SellMode;
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
        if (SellMode::ONLINE !== strtolower($command->sellMode())) {
            return;
        }

        $this->assertThatEventDoesNotExist($command);

        $events = $this->eventRepository->search(ByBaseEventIdCriteria::create($command->baseEventId()));

        $event = \current($events);

        if (false === $event) {
            $event = Event::create(
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
                $command->requestTime(),
                $command->organizerCompanyId(),
            );

            $this->eventRepository->add($event);

            $this->domainEventPublisher->execute($event);
            return;
        }

        $event = $event->update(
            $command->sellMode(),
            $command->title(),
            $command->eventStartDate(),
            $command->eventEndDate(),
            $command->eventId(),
            $command->sellFrom(),
            $command->sellTo(),
            $command->soldOut(),
            $command->zones(),
            $command->requestTime(),
            $command->organizerCompanyId(),
        );

        $this->eventRepository->update($event);

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
