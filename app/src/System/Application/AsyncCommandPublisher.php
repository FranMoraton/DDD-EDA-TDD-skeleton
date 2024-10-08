<?php

namespace App\System\Application;

use Symfony\Component\Messenger\MessageBusInterface;

final class AsyncCommandPublisher
{

    public function __construct(private MessageBusInterface $publishCommandBus)
    {
    }

    public function execute(Command ...$commands): void
    {
        foreach ($commands as $command) {
            $this->publishCommandBus->dispatch($command);
        }
    }
}