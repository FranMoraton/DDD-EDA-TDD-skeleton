<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Dbal;

use App\System\Domain\Criteria\Criteria;
use App\System\Domain\Criteria\Operator;
use Doctrine\DBAL\Query\QueryBuilder;

final class PostgresCriteriaTranslatorToDbal
{
    private const string TABLE_ALIAS = 't';
    private const string JSONB_ARRAY_MARKER = '[]';

    public function applyFilters(QueryBuilder $qb, Criteria $criteria): QueryBuilder
    {
        foreach ($criteria->filters() as $field => $filter) {
            $fieldName = (string) $field;
            $fieldType = $this->detectFieldType($fieldName);

            match ($fieldType) {
                'simple' => $this->applySimpleFilter($qb, $fieldName, $filter),
                'jsonb_object' => $this->applyJsonbObjectFilter($qb, $fieldName, $filter),
                'jsonb_array' => $this->applyJsonbArrayFilter($qb, $fieldName, $filter),
                default => throw new \InvalidArgumentException("Unknown field type: $fieldType"),
            };
        }

        return $qb;
    }

    public function applyPagination(QueryBuilder $qb, Criteria $criteria): QueryBuilder
    {
        if (null !== $criteria->limit()) {
            $qb->setMaxResults($criteria->limit());
        }

        if (null !== $criteria->offset()) {
            $qb->setFirstResult($criteria->offset());
        }

        return $qb;
    }

    public function applyOrder(QueryBuilder $qb, Criteria $criteria): QueryBuilder
    {
        if ($order = $criteria->order()) {
            $field = $order['field'];
            $fieldType = $this->detectFieldType($field);

            if ($fieldType === 'jsonb_array') {
                return $qb;
            }

            if ($fieldType === 'jsonb_object') {
                [$column, $nestedField] = explode('.', $field, 2);
                $qb->orderBy(
                    sprintf("(%s.%s->>'%s')", self::TABLE_ALIAS, $column, $nestedField),
                    $order['direction']
                );
                return $qb;
            }

            $qb->orderBy(self::TABLE_ALIAS . '.' . $field, $order['direction']);
        }

        return $qb;
    }

    public function buildCountQuery(QueryBuilder $qb, Criteria $criteria, string $tableName): QueryBuilder
    {
        $qb->select('COUNT(*) as total_count')
            ->from($tableName, self::TABLE_ALIAS);

        $this->applyFilters($qb, $criteria);

        return $qb;
    }

    public function buildSelectQuery(QueryBuilder $qb, Criteria $criteria, string $tableName): QueryBuilder
    {
        $hasJsonbArrayFilters = $this->hasJsonbArrayFilters($criteria);

        if ($hasJsonbArrayFilters) {
            $qb->select(sprintf('DISTINCT %s.*', self::TABLE_ALIAS));
        } else {
            $qb->select('*');
        }

        $qb->from($tableName, self::TABLE_ALIAS);

        $this->applyFilters($qb, $criteria);
        $this->applyOrder($qb, $criteria);
        $this->applyPagination($qb, $criteria);

        return $qb;
    }

    private function detectFieldType(string $field): string
    {
        if (str_contains($field, self::JSONB_ARRAY_MARKER)) {
            return 'jsonb_array';
        }

        if (str_contains($field, '.')) {
            return 'jsonb_object';
        }

        return 'simple';
    }

    private function hasJsonbArrayFilters(Criteria $criteria): bool
    {
        foreach (array_keys($criteria->filters()) as $field) {
            if ($this->detectFieldType((string) $field) === 'jsonb_array') {
                return true;
            }
        }

        return false;
    }

    private function parseJsonbArrayField(string $field): array
    {
        $normalized = str_replace(self::JSONB_ARRAY_MARKER, '', $field);
        return explode('.', $normalized, 2);
    }

    private function applySimpleFilter(QueryBuilder $qb, string $field, array $filter): void
    {
        $paramName = $this->sanitizeParamName($field);
        $operator = $filter['operator'];

        if ($operator === Operator::IN->value) {
            $this->applyInFilter($qb, $field, $paramName, $filter['value']);
            return;
        }

        if ($operator === Operator::IS_NULL->value || $operator === Operator::IS_NOT_NULL->value) {
            $qb->andWhere(sprintf('%s.%s %s', self::TABLE_ALIAS, $field, $operator));
            return;
        }

        $value = $this->formatValue($filter['value']);
        $qb->andWhere(sprintf('%s.%s %s :%s', self::TABLE_ALIAS, $field, $operator, $paramName))
            ->setParameter($paramName, $value);
    }

    private function applyInFilter(QueryBuilder $qb, string $field, string $paramName, array $values): void
    {
        $formattedValues = array_map(fn($v) => $this->formatValue($v), $values);

        $qb->andWhere(sprintf('%s.%s IN (:%s)', self::TABLE_ALIAS, $field, $paramName))
            ->setParameter($paramName, $formattedValues, \Doctrine\DBAL\ArrayParameterType::STRING);
    }

    private function applyJsonbObjectFilter(QueryBuilder $qb, string $field, array $filter): void
    {
        [$column, $nestedField] = explode('.', $field, 2);
        $paramName = $this->sanitizeParamName($field);
        $operator = $filter['operator'];

        if ($operator === Operator::IN->value) {
            $this->applyJsonbObjectInFilter($qb, $column, $nestedField, $paramName, $filter['value']);
            return;
        }

        if ($operator === Operator::IS_NULL->value) {
            $qb->andWhere(sprintf("(%s.%s->>'%s') IS NULL", self::TABLE_ALIAS, $column, $nestedField));
            return;
        }

        if ($operator === Operator::IS_NOT_NULL->value) {
            $qb->andWhere(sprintf("(%s.%s->>'%s') IS NOT NULL", self::TABLE_ALIAS, $column, $nestedField));
            return;
        }

        $value = $this->formatValue($filter['value']);

        if ($operator === Operator::EQUALS->value) {
            $qb->andWhere(sprintf(
                "(%s.%s->>'%s') = :%s",
                self::TABLE_ALIAS,
                $column,
                $nestedField,
                $paramName
            ));
        } else {
            $qb->andWhere(sprintf(
                "(%s.%s->>'%s')::text %s :%s",
                self::TABLE_ALIAS,
                $column,
                $nestedField,
                $operator,
                $paramName
            ));
        }

        $qb->setParameter($paramName, $value);
    }

    private function applyJsonbObjectInFilter(
        QueryBuilder $qb,
        string $column,
        string $nestedField,
        string $paramName,
        array $values
    ): void {
        $formattedValues = array_map(fn($v) => $this->formatValue($v), $values);

        $qb->andWhere(sprintf(
            "(%s.%s->>'%s') IN (:%s)",
            self::TABLE_ALIAS,
            $column,
            $nestedField,
            $paramName
        ));
        $qb->setParameter($paramName, $formattedValues, \Doctrine\DBAL\ArrayParameterType::STRING);
    }

    private function applyJsonbArrayFilter(QueryBuilder $qb, string $field, array $filter): void
    {
        [$column, $nestedField] = $this->parseJsonbArrayField($field);
        $paramName = $this->sanitizeParamName($field);
        $operator = $filter['operator'];

        if ($operator === Operator::IN->value) {
            $this->applyJsonbArrayInFilter($qb, $column, $nestedField, $paramName, $filter['value']);
            return;
        }

        $value = $this->formatValue($filter['value']);

        if ($operator === Operator::EQUALS->value) {
            $this->applyJsonbContainsFilter($qb, $column, $nestedField, $paramName, $filter['value']);
        } else {
            $this->applyJsonbComparisonFilter($qb, $column, $nestedField, $paramName, $operator, $value);
        }
    }

    private function applyJsonbArrayInFilter(
        QueryBuilder $qb,
        string $column,
        string $nestedField,
        string $paramName,
        array $values
    ): void {
        $formattedValues = array_map(fn($v) => $this->formatValue($v), $values);

        $existsClause = sprintf(
            "EXISTS (SELECT 1 FROM jsonb_array_elements(%s.%s) AS elem WHERE (elem->>'%s') IN (:%s))",
            self::TABLE_ALIAS,
            $column,
            $nestedField,
            $paramName
        );

        $qb->andWhere($existsClause);
        $qb->setParameter($paramName, $formattedValues, \Doctrine\DBAL\ArrayParameterType::STRING);
    }

    private function applyJsonbContainsFilter(
        QueryBuilder $qb,
        string $column,
        string $nestedField,
        string $paramName,
        mixed $value
    ): void {
        $qb->andWhere(sprintf(
            "%s.%s @> :%s::jsonb",
            self::TABLE_ALIAS,
            $column,
            $paramName
        ));

        $formattedValue = $this->formatValue($value);
        $jsonValue = json_encode([[$nestedField => $formattedValue]], JSON_THROW_ON_ERROR);
        $qb->setParameter($paramName, $jsonValue);
    }

    private function applyJsonbComparisonFilter(
        QueryBuilder $qb,
        string $column,
        string $nestedField,
        string $paramName,
        string $operator,
        mixed $value
    ): void {
        $existsClause = sprintf(
            "EXISTS (SELECT 1 FROM jsonb_array_elements(%s.%s) AS elem WHERE (elem->>'%s')::text %s :%s)",
            self::TABLE_ALIAS,
            $column,
            $nestedField,
            $operator,
            $paramName
        );

        $qb->andWhere($existsClause);
        $qb->setParameter($paramName, $value);
    }

    private function sanitizeParamName(string $field): string
    {
        return str_replace(['.', '[', ']'], '_', $field);
    }

    private function formatValue(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value;
    }

    public function translateToSql(Criteria $criteria, string $tableName): string
    {
        $conditions = [];
        $hasJsonbArrayFilters = false;

        foreach ($criteria->filters() as $field => $filter) {
            $fieldName = (string) $field;
            $fieldType = $this->detectFieldType($fieldName);

            if ($fieldType === 'jsonb_array') {
                $hasJsonbArrayFilters = true;
                $conditions[] = $this->buildJsonbArrayConditionSql($fieldName, $filter);
            } elseif ($fieldType === 'jsonb_object') {
                $conditions[] = $this->buildJsonbObjectConditionSql($fieldName, $filter);
            } else {
                $conditions[] = $this->buildSimpleConditionSql($fieldName, $filter);
            }
        }

        $select = $hasJsonbArrayFilters
            ? sprintf('SELECT DISTINCT %s.* FROM %s %s', self::TABLE_ALIAS, $tableName, self::TABLE_ALIAS)
            : sprintf('SELECT * FROM %s %s', $tableName, self::TABLE_ALIAS);

        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = ' WHERE ' . implode(' AND ', $conditions);
        }

        $orderClause = '';
        if ($order = $criteria->order()) {
            $fieldType = $this->detectFieldType($order['field']);
            if ($fieldType === 'simple') {
                $orderClause = sprintf(' ORDER BY %s.%s %s', self::TABLE_ALIAS, $order['field'], $order['direction']);
            } elseif ($fieldType === 'jsonb_object') {
                [$column, $nestedField] = explode('.', $order['field'], 2);
                $orderClause = sprintf(
                    " ORDER BY (%s.%s->>'%s') %s",
                    self::TABLE_ALIAS,
                    $column,
                    $nestedField,
                    $order['direction']
                );
            }
        }

        $limitClause = '';
        if (null !== $criteria->limit()) {
            $limitClause = sprintf(' LIMIT %d', $criteria->limit());
        }

        $offsetClause = '';
        if (null !== $criteria->offset()) {
            $offsetClause = sprintf(' OFFSET %d', $criteria->offset());
        }

        return $select . $whereClause . $orderClause . $limitClause . $offsetClause;
    }

    public function translateToCountSql(Criteria $criteria, string $tableName): string
    {
        $conditions = [];

        foreach ($criteria->filters() as $field => $filter) {
            $fieldName = (string) $field;
            $fieldType = $this->detectFieldType($fieldName);

            if ($fieldType === 'jsonb_array') {
                $conditions[] = $this->buildJsonbArrayConditionSql($fieldName, $filter);
            } elseif ($fieldType === 'jsonb_object') {
                $conditions[] = $this->buildJsonbObjectConditionSql($fieldName, $filter);
            } else {
                $conditions[] = $this->buildSimpleConditionSql($fieldName, $filter);
            }
        }

        $select = sprintf('SELECT COUNT(*) as total_count FROM %s %s', $tableName, self::TABLE_ALIAS);

        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = ' WHERE ' . implode(' AND ', $conditions);
        }

        return $select . $whereClause;
    }

    private function buildSimpleConditionSql(string $field, array $filter): string
    {
        $operator = $filter['operator'];

        if ($operator === Operator::IN->value) {
            return $this->buildInConditionSql($field, $filter['value']);
        }

        if ($operator === Operator::IS_NULL->value || $operator === Operator::IS_NOT_NULL->value) {
            return sprintf('%s.%s %s', self::TABLE_ALIAS, $field, $operator);
        }

        $value = $this->assertFilterValue($filter['value']);
        return sprintf('%s.%s %s %s', self::TABLE_ALIAS, $field, $operator, $this->formatValueForSql($value));
    }

    private function buildInConditionSql(string $field, array $values): string
    {
        $formattedValues = array_map(
            fn($v) => $this->formatValueForSql($this->assertFilterValue($v)),
            $values
        );
        return sprintf('%s.%s IN (%s)', self::TABLE_ALIAS, $field, implode(', ', $formattedValues));
    }

    private function buildJsonbObjectConditionSql(string $field, array $filter): string
    {
        [$column, $nestedField] = explode('.', $field, 2);
        $operator = $filter['operator'];

        if ($operator === Operator::IN->value) {
            return $this->buildJsonbObjectInConditionSql($column, $nestedField, $filter['value']);
        }

        if ($operator === Operator::IS_NULL->value) {
            return sprintf("(%s.%s->>'%s') IS NULL", self::TABLE_ALIAS, $column, $nestedField);
        }

        if ($operator === Operator::IS_NOT_NULL->value) {
            return sprintf("(%s.%s->>'%s') IS NOT NULL", self::TABLE_ALIAS, $column, $nestedField);
        }

        $value = $this->assertFilterValue($filter['value']);
        $formattedValue = $this->formatValueForSql($value);

        if ($operator === Operator::EQUALS->value) {
            return sprintf("(%s.%s->>'%s') = %s", self::TABLE_ALIAS, $column, $nestedField, $formattedValue);
        }

        return sprintf("(%s.%s->>'%s')::text %s %s", self::TABLE_ALIAS, $column, $nestedField, $operator, $formattedValue);
    }

    private function buildJsonbObjectInConditionSql(string $column, string $nestedField, array $values): string
    {
        $formattedValues = array_map(
            fn($v) => $this->formatValueForSql($this->assertFilterValue($v)),
            $values
        );
        return sprintf(
            "(%s.%s->>'%s') IN (%s)",
            self::TABLE_ALIAS,
            $column,
            $nestedField,
            implode(', ', $formattedValues)
        );
    }

    private function buildJsonbArrayConditionSql(string $field, array $filter): string
    {
        [$column, $nestedField] = $this->parseJsonbArrayField($field);
        $operator = $filter['operator'];

        if ($operator === Operator::IN->value) {
            return $this->buildJsonbArrayInConditionSql($column, $nestedField, $filter['value']);
        }

        if ($operator === Operator::EQUALS->value) {
            $formattedValue = $this->formatValue($filter['value']);
            $jsonValue = json_encode([[$nestedField => $formattedValue]], JSON_THROW_ON_ERROR);
            return sprintf("%s.%s @> '%s'::jsonb", self::TABLE_ALIAS, $column, $jsonValue);
        }

        $value = $this->assertFilterValue($filter['value']);
        return sprintf(
            "EXISTS (SELECT 1 FROM jsonb_array_elements(%s.%s) AS elem WHERE (elem->>'%s')::text %s %s)",
            self::TABLE_ALIAS,
            $column,
            $nestedField,
            $operator,
            $this->formatValueForSql($value)
        );
    }

    private function buildJsonbArrayInConditionSql(string $column, string $nestedField, array $values): string
    {
        $formattedValues = array_map(
            fn($v) => $this->formatValueForSql($this->assertFilterValue($v)),
            $values
        );
        return sprintf(
            "EXISTS (SELECT 1 FROM jsonb_array_elements(%s.%s) AS elem WHERE (elem->>'%s') IN (%s))",
            self::TABLE_ALIAS,
            $column,
            $nestedField,
            implode(', ', $formattedValues)
        );
    }

    private function assertFilterValue(mixed $value): int|bool|string|float|\DateTimeInterface
    {
        \assert(
            is_int($value) || is_bool($value) || is_string($value) || is_float($value) || $value instanceof \DateTimeInterface
        );

        return $value;
    }

    private function formatValueForSql(int|bool|string|float|\DateTimeInterface $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return sprintf("'%s'", $value->format('Y-m-d H:i:s'));
        }

        if (is_string($value)) {
            return sprintf("'%s'", $value);
        }

        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        return (string) $value;
    }
}
