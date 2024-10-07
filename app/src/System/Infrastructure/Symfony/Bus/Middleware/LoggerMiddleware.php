<?php
declare(strict_types=1);

namespace App\System\Infrastructure\Symfony\Bus\Middleware;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;

final class LoggerMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        try {
            $result = $stack->next()->handle($envelope, $stack);
        } catch (\Throwable $e) {
            $context = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
                'message' => $e->getMessage(),
                'status' => $e->getCode(),
                'data' => \json_decode(\json_encode($e), true)
            ];
            $this->logger->error(
                'command_failed',
                $context
            );

            throw $e;
        }

        return $result;
    }
}
