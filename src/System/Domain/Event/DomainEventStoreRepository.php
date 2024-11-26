<?php

declare(strict_types=1);

namespace App\System\Domain\Event;

interface DomainEventStoreRepository
{
    public function add(DomainEvent $event): void;
}
