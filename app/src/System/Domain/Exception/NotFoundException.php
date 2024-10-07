<?php

declare(strict_types=1);

namespace App\System\Domain\Exception;

use DomainException;

final class NotFoundException extends DomainException implements \JsonSerializable
{
    private const string TYPE = 'NOT_FOUND';

    private array $exceptionData;

    public function __construct(
        private readonly string $modelClass,
        private readonly string $prefixMessage,
        private readonly array $data,
        private readonly ?\Throwable $previous = null,
    ) {
        $this->exceptionData = $this->buildData($this->modelClass, $this->data);

        parent::__construct(
            $this->prefixMessage . ' not found',
            404,
            $this->previous,
        );
    }

    public function modelClass(): string
    {
        return $this->modelClass;
    }

    public function jsonSerialize(): array
    {
        return $this->exceptionData;
    }

    private function buildData(string $modelClass, array $data): array
    {
        return [
            'type' => self::TYPE,
            'model' => $modelClass,
            'data' => $data,
        ];
    }
}
