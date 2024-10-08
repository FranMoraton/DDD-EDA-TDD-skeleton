<?php

declare(strict_types=1);

namespace App\System\Application;

use App\System\Domain\Model\Aggregate;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class DomainEventPublisher
{
    public function __construct(private MessageBusInterface $eventBus)
    {
    }

    public function execute(?Aggregate ...$models): void
    {
        foreach ($models as $model) {
            if (null === $model) {
                continue;
            }

            foreach ($model->events() as $event) {
                $this->eventBus->dispatch($event);
            }
        }
    }
}
