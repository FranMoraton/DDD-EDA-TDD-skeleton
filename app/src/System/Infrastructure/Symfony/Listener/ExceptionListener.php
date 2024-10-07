<?php

namespace App\System\Infrastructure\Symfony\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

class ExceptionListener implements EventSubscriberInterface
{
    /**
     * @throws \Throwable
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!($exception instanceof HandlerFailedException)) {
            return;
        }

        $exception = $exception->getPrevious();

        if (null === $exception) {
            return;
        }

        $data = [
            'message' => $exception->getMessage(),
            'status' => $exception->getCode(),
            'data' => \json_decode(\json_encode($exception), true)
        ];

        $response = new JsonResponse($data);
        $response->setStatusCode($exception->getCode());

        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['onKernelException', 100],
            ],
        ];
    }
}