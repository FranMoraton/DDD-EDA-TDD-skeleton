<?php
declare(strict_types=1);

namespace App\System\Infrastructure\Datadog;

use App\System\Domain\ValueObject\Uuid;

final class DatadogService
{
    /** @var array<\OpenTracing\Scope> */
    private array $scopes = [];

    public function startSpan(string $transactionName, array $tags): ?Uuid
    {
        if (false === $this->isDatadogInstalled()) {
            return null;
        }

        $spanId = Uuid::v4();

        $scope = $this->getTracer()->startActiveSpan($transactionName);

        foreach ($tags as $key => $value) {
            $scope->getSpan()->setTag($key, $value);
        }

        $this->scopes[$spanId->value()] = $scope;

        return $spanId;
    }

    public function endSpan(Uuid $spanId): void
    {
        if (false === $this->isDatadogInstalled()) {
            return;
        }

        $spanIdAsString = $spanId->value();

        if (false === \array_key_exists($spanIdAsString, $this->scopes)) {
            return;
        }

        $this->scopes[$spanIdAsString]->close();

        unset($this->scopes[$spanIdAsString]);
    }

    public function noticeError(Uuid $spanId, \Throwable $throwable): void
    {
        if (false === $this->isDatadogInstalled()) {
            return;
        }

        $spanIdAsString = $spanId->value();

        if (false === \array_key_exists($spanIdAsString, $this->scopes)) {
            return;
        }

        $span = $this->scopes[$spanIdAsString]->getSpan();
        $span->setTag('error.type', $throwable::class);
        $span->setTag('error.msg', $throwable->getMessage());
        $span->setTag('error.stack', $throwable->getTraceAsString());
    }

    public function isDatadogInstalled(): bool
    {
        return true === \extension_loaded('ddtrace') && \class_exists('\DDTrace\GlobalTracer');
    }

    public function getCurrentContextIfAvailable(): ?array
    {
        if (false === $this->isDatadogInstalled()) {
            return [];
        }

        $context = \DDTrace\current_context();

        return [
            'trace_id' => $context['trace_id'],
            'span_id' => $context['span_id'],
        ];
    }

    /** @return \OpenTracing\Tracer */
    private function getTracer()
    {
        return \DDTrace\GlobalTracer::get();
    }
}
