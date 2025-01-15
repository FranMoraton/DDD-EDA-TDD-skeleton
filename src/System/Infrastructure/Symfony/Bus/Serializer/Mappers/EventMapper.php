<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Symfony\Bus\Serializer\Mappers;

use App\System\Domain\Event\DomainEvent;

final class EventMapper
{
    protected array $events = [];

    public function add(string $name, string $className): void
    {
        if (false === \is_a($className, DomainEvent::class, true)) {
            return;
        }

        if (true === \array_key_exists($name, $this->events)) {
            throw new \RuntimeException(\sprintf(
                'Event <%s> has already been registered. Maybe you forgot to update the use case name',
                $name,
            ));
        }

        $this->events[$name] = $className;
    }

    public function get(string $name): ?string
    {
        return $this->events[$name] ?? null;
    }

    public function list(): array
    {
        return $this->events;
    }
}
