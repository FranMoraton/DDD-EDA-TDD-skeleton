<?php

namespace App\Tests\System\Infrastructure\Behat;

use App\System\Application\Message;
use App\System\Domain\Event\DomainEvent;
use App\System\Infrastructure\Symfony\Bus\Middleware\SpyMiddleware;
use Behat\Behat\Context\Context;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class ResponseManager implements Context
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $eventBus,
        private readonly KernelInterface $kernel,
        private readonly RequestHistory $requestHistory,
        private readonly SpyMiddleware $spyMiddleware,
    ) {
    }

    public function sendRequest(Request $request): void
    {
        $response = $this->kernel->handle($request);
        $this->requestHistory->add($request, $response);
        $this->kernel->terminate($request, $response);
    }

    public function sendMessage(Message $request): void
    {
        $this->commandBus->dispatch($request);
    }

    public function sendEvent(DomainEvent $message): void
    {
        $this->eventBus->dispatch($message);
    }

    public function getResponse(): ?Response
    {
        return $this->requestHistory->getLastResponse();
    }

    public function getLatestSpyMiddleware(): SpyMiddleware
    {
        return $this->spyMiddleware;
    }
}
