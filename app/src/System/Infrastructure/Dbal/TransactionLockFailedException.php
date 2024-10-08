<?php
declare(strict_types=1);

namespace App\System\Infrastructure\Dbal;

use App\System\Domain\Exception\ExceptionCode;

final class TransactionLockFailedException extends \Exception implements \JsonSerializable
{
    private function __construct(
        string $message,
        private string $modelName,
        private array $data,
        private array $criteria,
    ) {
        parent::__construct($message, ExceptionCode::ValidationError->toHttpCode());
    }

    public static function from(
        string $modelName,
        array $data,
        array $criteria,
    ): self {
        return new self(
            'Transaction Lock Failed',
            $modelName,
            $data,
            $criteria,
        );
    }

    public function data(): array
    {
        return [
            'model_name' => $this->modelName,
            'data' => $this->data,
            'criteria' => $this->criteria,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->data();
    }
}
