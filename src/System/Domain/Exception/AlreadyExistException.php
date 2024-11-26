<?php

declare(strict_types=1);

namespace App\System\Domain\Exception;

final class AlreadyExistException extends DomainException
{
    private const string TYPE = 'ALREADY_EXISTS';

    public function __construct(
        private readonly string $modelClass,
        private readonly string $prefixMessage,
        private readonly array $data,
    ) {
        parent::__construct(
            $this->prefixMessage . ' already exists',
            ExceptionCode::BadBusinessLogic,
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
