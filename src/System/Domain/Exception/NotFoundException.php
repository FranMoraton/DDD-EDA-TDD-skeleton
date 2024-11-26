<?php

declare(strict_types=1);

namespace App\System\Domain\Exception;

final class NotFoundException extends DomainException
{
    private const string TYPE = 'NOT_FOUND';

    public function __construct(
        private readonly string $modelClass,
        private readonly string $prefixMessage,
        private readonly array $data,
    ) {
        parent::__construct(
            $this->prefixMessage . ' not found',
            ExceptionCode::NotFound,
            $this->buildData($this->modelClass, $this->data),
        );
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
