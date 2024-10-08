<?php
declare(strict_types=1);

namespace App\System\Infrastructure\Symfony\Bus\Middleware;

use App\System\Domain\Exception\DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final readonly class LoggerMiddleware implements MiddlewareInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        try {
            $result = $stack->next()->handle($envelope, $stack);
        } catch (DomainException $e) {
            $context = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
                'message' => $e->getMessage(),
                'status' => $e->getCode(),
                'data' => $e->payload()
            ];
            $this->logger->error(
                'command_failed',
                $context
            );

            throw $e;
        } catch (\Throwable $e) {
            $context = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
                'message' => $e->getMessage(),
                'status' => $e->getCode(),
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
