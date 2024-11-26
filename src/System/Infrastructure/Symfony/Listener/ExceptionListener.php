<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Symfony\Listener;

use App\System\Domain\Exception\DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class ExceptionListener
{
    public function __construct(private readonly string $env)
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        match (true) {
            $exception instanceof DomainException => $this->changeResponse(
                $event,
                $this->responseFromDomainException(...)
            ),
            default => $this->changeResponse(
                $event,
                $this->responseFromInternalException(...)
            ),
        };
    }

    public function changeResponse(ExceptionEvent $event, callable $getNewResponse): void
    {
        $getNewResponse($event->getThrowable());

        $event->setResponse($getNewResponse($event->getThrowable()));
    }

    public function responseFromDomainException(DomainException $exception): JsonResponse
    {
        $content = [
            'message' => $exception->getMessage(),
            'payload' => $exception->payload()
        ];

        return new JsonResponse($content, $exception->domainExceptionCode()->toHttpCode());
    }

    public function responseFromInternalException(\Throwable $throwable): JsonResponse
    {
        if ('prod' === $this->env) {
            $content = [
                'message' => 'Something went wrong'
            ];

            return new JsonResponse($content, 500);
        }

        $content = [
            'real' => $throwable->getMessage(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => $throwable->getTrace(),
        ];

        return new JsonResponse($content, 500);
    }
}
