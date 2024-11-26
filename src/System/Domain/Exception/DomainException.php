<?php

declare(strict_types=1);

namespace App\System\Domain\Exception;

class DomainException extends \DomainException
{
    /**
     * @param array<mixed> $payload
     */
    public function __construct(
        string $reason,
        private readonly ExceptionCode $domainExceptionCode,
        private readonly array $payload = []
    ) {
        parent::__construct($reason);
    }

    public function domainExceptionCode(): ExceptionCode
    {
        return $this->domainExceptionCode;
    }

    /**
     * @return array<mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
