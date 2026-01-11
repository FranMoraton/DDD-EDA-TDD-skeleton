<?php
 
declare(strict_types=1);

namespace App\System\Infrastructure\Symfony\Listener;

use App\System\Domain\Exception\DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\System\Infrastructure\Dbal\TransactionLockFailedException;
use Psr\Log\LoggerInterface;
use Monolog\Attribute\WithMonologChannel;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;

#[WithMonologChannel('uncaught_exception')]
final readonly class ExceptionListener
{
    public function __construct(private readonly string $env, private readonly LoggerInterface $logger)
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $response = match (true) {
            $exception instanceof DomainException => $this->responseFromDomainException($exception),
            $exception instanceof \InvalidArgumentException => $this->buildErrorResponse(
                Response::HTTP_BAD_REQUEST,
                'InvalidArgumentException',
                $exception,
            ),
            $exception instanceof AccessDeniedHttpException => $this->buildErrorResponse(
                Response::HTTP_FORBIDDEN,
                'AccessDenied',
                $exception,
            ),
            $exception instanceof AuthenticationException => $this->buildErrorResponse(
                Response::HTTP_UNAUTHORIZED,
                'Unauthorized',
                $exception,
            ),
            $exception instanceof NotFoundHttpException => $this->buildErrorResponse(
                Response::HTTP_NOT_FOUND,
                'NotFound',
                $exception,
            ),
            $exception instanceof TransactionLockFailedException => $this->buildErrorResponse(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'TransactionLockFailed',
                $exception,
            ),
            default => $this->buildErrorResponse(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'InternalServerError',
                $exception,
            ),
        };

        $event->setResponse($response);
    }

    private function responseFromDomainException(DomainException $exception): JsonResponse
    {
        $content = [
            'error' => [
                'code' => $exception->domainExceptionCode()->toHttpCode(),
                'message' => $exception->getMessage(),
            ],
            'data' => $exception->payload()
        ];

        return new JsonResponse($content, $exception->domainExceptionCode()->toHttpCode());
    }

    private function buildErrorResponse(int $statusCode, string $message, \Throwable $throwable): JsonResponse
    {
        $content =[
            'error' => [
                'code' => $statusCode,
                'message' => $message
            ],
            'data' => [
                'real' => $throwable->getMessage(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'trace' => $throwable->getTraceAsString(),
            ],
        ];
        
        if ($this->env !== 'test') {
            $this->logger->error(
                'uncaught_exception (' . $message . ')',
                $content,
            );            
        }

        if (true === in_array($this->env, ['prod', 'test'])) {
            $content['data'] = null;
        }

        return new JsonResponse($content, $statusCode);
    }
}
