<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Symfony\Bus\Middleware;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final readonly class SimpleMessagePublisherMiddleware implements MiddlewareInterface
{
    public function __construct(private MessageBusInterface $asyncCommandBus)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $resultStack = $stack->next()->handle($envelope, $stack);
        $commandsResult = $this->extractHandledStamp($resultStack);

        if ((\is_countable($commandsResult) && 0 === \count($commandsResult))) {
            return $resultStack;
        }

        foreach ($commandsResult as $commands) {
            if (null === $commands) {
                continue;
            }
            // We can accept both one or an array of commands to be dispatched
            if (false === \is_array($commands)) {
                $commands = [$commands];
            }

            foreach ($commands as $theCommand) {
                if (false === \is_object($theCommand)) {
                    continue;
                }

                $this->asyncCommandBus->dispatch($theCommand);
            }
        }

        return $resultStack;
    }

    private function extractHandledStamp(Envelope $envelope): array
    {
        $results = [];

        foreach ($envelope->all() as $key => $stamp) {
            if (HandledStamp::class !== $key) {
                continue;
            }

            foreach ($stamp as $resultStamp) {
                \assert($resultStamp instanceof HandledStamp);
                $results[] = $resultStamp->getResult();
            }
        }

        return $results;
    }
}
