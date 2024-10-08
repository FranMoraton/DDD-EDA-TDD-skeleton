<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Symfony\Bus\Middleware;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;


final class ForceSyncMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $envelope = $envelope->with(
            new ReceivedStamp('sync'),
        );

        return $stack->next()->handle($envelope, $stack);
    }
}
