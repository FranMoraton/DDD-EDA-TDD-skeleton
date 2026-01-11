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

    private const array VALUELESS_OPERATORS = [
        Operator::IS_NULL,
        Operator::IS_NOT_NULL,
    ];

    public function withFilter(
        string $field,
        int|bool|string|float|\DateTimeImmutable|array $value,
        Operator $operator
    ): self {
        $allowedFields = $this->allowedFields();

        if (false === array_key_exists($field, $allowedFields)) {
            throw new \InvalidArgumentException("The field '$field' is not allowed.");
        }

        // Skip type validation for valueless operators (IS_NULL, IS_NOT_NULL)
        if (!in_array($operator, self::VALUELESS_OPERATORS, true)) {
            $type = $allowedFields[$field];

            if ($operator === Operator::IN) {
                $this->assertInFilterValue($value, $type, $field);
            } else {
                if (is_array($value)) {
                    throw new \InvalidArgumentException("Array values are only allowed with IN operator.");
                }
                $this->assertValueType($value, $type, $field);
            }
        }

        $this->filters[$field] = [
            'value' => $value,
            'operator' => $operator->value
        ];

        return $this;
    }

    public function withLimit(?int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function withOffset(?int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function withOrder(string $field, Direction $direction): self
    {
        $allowedFields = $this->allowedFields();
        $orderField = $this->normalizeFieldForOrder($field);

        if (false === \array_key_exists($field, $allowedFields) && false === $this->isSimpleColumn($field)) {
            throw new \InvalidArgumentException("The field '$field' is not allowed.");
        }

        $this->orderBy = $orderField;
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
        int|bool|string|float|\DateTimeImmutable $value,
        string $type,
        string $field,
    ): void {
        $isValid = match ($type) {
            'int' => is_int($value),
            'float' => is_float($value),
            'bool' => is_bool($value),
            'datetime' => $value instanceof \DateTimeImmutable,
            'string' => is_string($value),
            default => throw new \InvalidArgumentException("Unsupported type: $type"),
        };

        if (false === $isValid) {
            $actualType = gettype($value);
            throw new \InvalidArgumentException("The field '$field' must be of type '$type', '$actualType' was given.");
        }
    }

    protected function assertInFilterValue(mixed $values, string $type, string $field): void
    {
        if (!is_array($values)) {
            throw new \InvalidArgumentException("IN operator requires an array value.");
        }

        if (empty($values)) {
            throw new \InvalidArgumentException("The values array for IN filter cannot be empty.");
        }

        foreach ($values as $value) {
            $this->assertValueType($value, $type, $field);
        }
    }

    public function calculatePaginationOffSet(int $page, int $itemsPerPage): int
    {
        \assert($page > 0);
        \assert($itemsPerPage > 0);

        return ($page - 1) * $itemsPerPage;
    }

    private function isSimpleColumn(string $field): bool
    {
        return !str_contains($field, '.') && !str_contains($field, '[]');
    }

    private function normalizeFieldForOrder(string $field): string
    {
        return str_replace('[]', '', $field);
    }

    public function getFieldType(string $field): ?string
    {
        return $this->allowedFields()[$field] ?? null;
    }

    public function isFieldAllowed(string $field): bool
    {
        return array_key_exists($field, $this->allowedFields());
    }
}
