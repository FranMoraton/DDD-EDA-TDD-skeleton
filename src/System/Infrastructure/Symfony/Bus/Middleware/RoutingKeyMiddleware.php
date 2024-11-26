<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Symfony\Bus\Middleware;

use App\System\Application\Command;
use App\System\Application\Query;
use App\System\Domain\Event\DomainEvent;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class RoutingKeyMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        \assert(
            $message instanceof DomainEvent
            || $message instanceof Command
            || $message instanceof Query
        );
        $envelope = $envelope->with(
            new AmqpStamp(
                $message::messageName(),
            ),
        );

        return $stack->next()->handle($envelope, $stack);
    }
}
