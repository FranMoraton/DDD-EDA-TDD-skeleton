<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Symfony\Bus\Middleware;

use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class RoutingKeyMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        $envelope = $envelope->with(
            new AmqpStamp(
                $message::messageName(),
            ),
        );

        return $stack->next()->handle($envelope, $stack);
    }
}
