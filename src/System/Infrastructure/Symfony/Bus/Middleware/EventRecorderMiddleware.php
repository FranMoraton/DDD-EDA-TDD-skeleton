<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Symfony\Bus\Middleware;

use App\System\Domain\Event\DomainEvent;
use App\System\Domain\Event\DomainEventStoreRepository;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class EventRecorderMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly DomainEventStoreRepository $eventStore)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        if ($message instanceof DomainEvent) {
            $this->eventStore->add($message);
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
