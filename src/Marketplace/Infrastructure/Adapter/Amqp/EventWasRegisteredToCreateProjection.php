<?php

declare(strict_types=1);

namespace App\Marketplace\Infrastructure\Adapter\Amqp;

use App\Marketplace\Domain\Model\Event\Event\EventWasCreated;

final class EventWasRegisteredToCreateProjection
{
    public function __invoke(EventWasCreated $eventWasCreated): null
    {
        return null;
    }
}
