<?php

declare(strict_types=1);

namespace App\Marketplace\Infrastructure\Adapter\Amqp;

use App\Marketplace\Application\Command\Events\Register\RegisterEventCommand;
use App\Marketplace\Domain\Model\Event\Event\EventWasCreated;

final class EventWasRegisteredToByIdQuery
{
    public function __invoke(EventWasCreated $eventWasCreated): RegisterEventCommand
    {
        return RegisterEventCommand::create($eventWasCreated->aggregateId()->value());
    }
}
