<?php

namespace App\System\Domain\Model;

use App\System\Domain\Event\DomainEvent;

#[\AllowDynamicProperties]
abstract class Aggregate
{
    private array $events;

    protected function __construct()
    {
        $this->events = [];
    }

    abstract public static function modelName(): string;

    final public function events(): array
    {
        return $this->events;
    }

    final protected function recordThat(DomainEvent $event): void
    {
        $this->events[] = $event;
    }
}