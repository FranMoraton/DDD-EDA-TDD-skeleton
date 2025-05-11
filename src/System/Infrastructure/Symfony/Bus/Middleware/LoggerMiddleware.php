<?php
 
declare(strict_types=1);

namespace App\System\Infrastructure\Symfony\Bus\Middleware;

use App\System\Application\Message;
use App\System\Domain\Exception\DomainException;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;

#[WithMonologChannel('command')]
final readonly class LoggerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        try {
            $result = $stack->next()->handle($envelope, $stack);
        } catch (DomainException $e) {
            \assert($envelope->getMessage() instanceof Message);

            $context = [
                'message_name' => $envelope->getMessage()::messageName(),
                'message_class' => $envelope->getMessage()::class,
                'message_payload' => $envelope->getMessage()->payload(),
                'retry' => $envelope->last(RedeliveryStamp::class)?->getRetryCount() ?? 0,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'message' => $e->getMessage(),
                'status' => $e->getCode(),
                'data' => $e->payload()
            ];

            $this->logger->error(
                $envelope->getMessage()::class,
                $context
            );

            throw $e;
        } catch (\Throwable $e) {
            \assert($envelope->getMessage() instanceof Message);

            $context = [
                'message_name' => $envelope->getMessage()::messageName(),
                'message_class' => $envelope->getMessage()::class,
                'message_payload' => $envelope->getMessage()->payload(),
                'retry' => $envelope->last(RedeliveryStamp::class)?->getRetryCount() ?? 0,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'message' => $e->getMessage(),
                'status' => $e->getCode(),
            ];
            $this->logger->error(
                $envelope->getMessage()::class,
                $context
            );

            throw $e;
        }

        return $result;
    }
}
