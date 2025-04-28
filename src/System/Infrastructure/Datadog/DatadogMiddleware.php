<?php
declare(strict_types=1);

namespace App\System\Infrastructure\Datadog;

use App\System\Domain\ValueObject\Uuid;
use App\System\Application\Message;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class DatadogMiddleware implements MiddlewareInterface
{
    private const SERVICE_TYPE = 'messenger';

    public function __construct(private DatadogService $datadog, private string $operation)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $this->messageFromEnvelope($envelope);

        if (null === $message || false === $this->datadog->isDatadogInstalled()) {
            return $stack->next()->handle($envelope, $stack);
        }

        $message = $this->messageFromEnvelope($envelope);
        \assert($message instanceof Message);

        $spanId = $this->datadog->startSpan(
            $this->operation,
            [
                'resource.name' => $message::messageName(),
                'span.type' => $message::class,
                'service.name' => self::SERVICE_TYPE,
            ],
        );
        \assert($spanId instanceof Uuid);

        try {
            $envelope = $stack->next()->handle($envelope, $stack);
        } catch (\Throwable $exception) {
            $this->datadog->noticeError($spanId, $exception);
            $this->datadog->endSpan($spanId);

            throw $exception;
        }

        $this->datadog->endSpan($spanId);

        return $envelope;
    }

    private function messageFromEnvelope(Envelope $envelope): ?Message
    {
        $message = $envelope->getMessage();

        return $message instanceof Message ? $message : null;
    }
}
