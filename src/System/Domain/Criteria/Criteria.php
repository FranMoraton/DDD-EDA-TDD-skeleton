<?php

declare(strict_types=1);

namespace App\System\Domain\Criteria;

abstract class Criteria
{
    protected array $filters = [];

    protected ?int $limit = null;
    protected ?int $offset = null;
    protected ?string $orderBy = null;
    protected ?Direction $direction = null;

    abstract protected function allowedFields(): array;

    public function withFilter(
        string $field,
        int|bool|string|float|\DateTime $value,
        Operator $operator
    ): self {
        $allowedFields = $this->allowedFields();

        if (false === array_key_exists($field, $allowedFields)) {
            throw new \InvalidArgumentException("The field '$field' is not allowed.");
        }

        $type = $allowedFields[$field];

        $this->assertValueType($value, $type, $field);

        $this->filters[$field] = [
            'value' => $value,
            'operator' => $operator->value
        ];

        return $this;
    }

    public function withLimit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function withOffset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function withOrder(string $field, Direction $direction): self
    {
        if (false === \array_key_exists($field, $this->allowedFields())) {
            throw new \InvalidArgumentException("The field '$field' is not allowed.");
        }


        $this->orderBy = $field;
        $this->direction = $direction;

        return $this;
    }

    public function filters(): array
    {
        return $this->filters;
    }

    public function limit(): ?int
    {
        return $this->limit;
    }

    public function offset(): ?int
    {
        return $this->offset;
    }

    public function order(): ?array
    {
        return $this->orderBy !== null
            ? ['field' => $this->orderBy, 'direction' => $this->direction->value ?? Direction::ASC->value]
            : null;
    }

    protected function assertValueType(
        int|bool|string|float|\DateTime $value,
        string $type,
        string $field,
    ): void {
        $isValid = match ($type) {
            'int' => is_int($value),
            'float' => is_float($value),
            'bool' => is_bool($value),
            'datetime' => $value instanceof \DateTime,
            'string' => is_string($value),
            default => throw new \InvalidArgumentException("Unsupported type: $type"),
        };

        if (false === $isValid) {
            $actualType = gettype($value);
            throw new \InvalidArgumentException("The field '$field' must be of type '$type', '$actualType' was given.");
        }
    }
}
