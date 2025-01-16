<?php
declare(strict_types=1);

namespace App\System\Infrastructure\Symfony\Bus\Middleware;

use App\System\Application\Message;
use App\System\Domain\Event\DomainEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class SpyMiddleware implements MiddlewareInterface
{
    /** @var array<DomainEvent> */
    private array $events = [];

    /** @var array<Message> */
    private array $commands = [];

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        if ($message instanceof DomainEvent) {
            $this->events[] = $message;
        } elseif ($message instanceof Message) {
            $this->commands[] = $message;
        }

        return $stack->next()->handle($envelope, $stack);
    }

    /** @return array<DomainEvent> */
    public function getEvents(): array
    {
        return $this->events;
    }

    /** @return array<Message> */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * @throws \Exception
     */
    public function hasCommand(string $eventName, int $expectedOccurrences = 1): void
    {
        $occurrences = 0;

        foreach ($this->commands as $request) {
            if ($eventName === $request::messageName()) {
                $occurrences++;
            }
        }

        if ($occurrences !== $expectedOccurrences) {
            throw new \Exception(\sprintf(
                'Expecting <%s> occurrences of request <%s> but <%d> found',
                $expectedOccurrences,
                $eventName,
                $occurrences,
            ));
        }
    }

    /**
     * @throws \Exception
     */
    public function hasEvent(string $eventName, int $expectedOccurrences = 1): void
    {
        $occurrences = 0;

        foreach ($this->events as $event) {
            if ($eventName === $event::messageName()) {
                $occurrences++;
            }
        }

        if ($occurrences !== $expectedOccurrences) {
            throw new \Exception(\sprintf(
                'Expecting <%s> occurrences of event <%s> but <%d> found',
                $expectedOccurrences,
                $eventName,
                $occurrences,
            ));
        }
    }

    public function clean(): void
    {
        $this->events = [];
        $this->commands = [];
    }
}
