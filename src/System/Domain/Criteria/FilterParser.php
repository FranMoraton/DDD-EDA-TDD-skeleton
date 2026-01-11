<?php

declare(strict_types=1);

namespace App\System\Domain\Criteria;

final class FilterParser
{
    private const string OPERATOR_SEPARATOR = '::';
    private const string VALUE_SEPARATOR = ',';

    private const array OPERATOR_MAP = [
        'EQUALS' => Operator::EQUALS,
        'NOT_EQUALS' => Operator::NOT_EQUALS,
        'GREATER_THAN' => Operator::GREATER_THAN,
        'LESS_THAN' => Operator::LESS_THAN,
        'GREATER_THAN_OR_EQUALS' => Operator::GREATER_THAN_OR_EQUALS,
        'LESS_THAN_OR_EQUALS' => Operator::LESS_THAN_OR_EQUALS,
        'LIKE' => Operator::LIKE,
        'IN' => Operator::IN,
        'IS_NULL' => Operator::IS_NULL,
        'IS_NOT_NULL' => Operator::IS_NOT_NULL,
    ];

    public static function applyFilters(Criteria $criteria, array $filters): void
    {
        foreach ($filters as $field => $rawValue) {
            $fieldName = (string) $field;

            if (!$criteria->isFieldAllowed($fieldName)) {
                continue;
            }

            $stringValue = $rawValue !== null ? (string) $rawValue : '';

            // Check for valueless operators first (IS_NULL, IS_NOT_NULL)
            $operator = self::parseValuelessOperator($stringValue);
            if (null !== $operator) {
                $criteria->withFilter($fieldName, true, $operator);
                continue;
            }

            // Skip null/empty for operators that require values
            if (null === $rawValue || '' === $rawValue) {
                continue;
            }

            [$operator, $value] = self::parseFilterValue($stringValue);

            if ($operator === Operator::IN) {
                $values = self::parseInValues($value, $criteria->getFieldType($fieldName));
                $criteria->withFilter($fieldName, $values, $operator);
            } else {
                $typedValue = self::castValue($value, $criteria->getFieldType($fieldName));
                $criteria->withFilter($fieldName, $typedValue, $operator);
            }
        }
    }

    private static function parseValuelessOperator(string $rawValue): ?Operator
    {
        $normalized = strtoupper(trim($rawValue));

        return match ($normalized) {
            'IS_NULL' => Operator::IS_NULL,
            'IS_NOT_NULL' => Operator::IS_NOT_NULL,
            default => null,
        };
    }

    /**
     * @return array{Operator, string}
     */
    private static function parseFilterValue(string $rawValue): array
    {
        if (!str_contains($rawValue, self::OPERATOR_SEPARATOR)) {
            return [Operator::EQUALS, $rawValue];
        }

        $parts = explode(self::OPERATOR_SEPARATOR, $rawValue, 2);
        $operatorName = strtoupper($parts[0]);
        $value = $parts[1] ?? '';

        if (!isset(self::OPERATOR_MAP[$operatorName])) {
            return [Operator::EQUALS, $rawValue];
        }

        return [self::OPERATOR_MAP[$operatorName], $value];
    }

    private static function parseInValues(string $value, ?string $type): array
    {
        $values = explode(self::VALUE_SEPARATOR, $value);

        return array_map(
            fn(string $v) => self::castValue(trim($v), $type),
            $values
        );
    }

    private static function castValue(string $value, ?string $type): int|bool|string|float|\DateTimeImmutable
    {
        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'datetime' => new \DateTimeImmutable($value),
            default => $value,
        };
    }
}
