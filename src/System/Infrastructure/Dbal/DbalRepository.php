<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Dbal;

use App\System\Domain\Criteria\Criteria;
use App\System\Domain\ValueObject\DateTimeValueObject;
use Doctrine\DBAL\Connection;

abstract class DbalRepository
{
    private const VERSION_FIELD = 'version';
    private const UPDATED_AT_FIELD = 'updated_at';
    private const CONTROL_FIELDS = [
        self::VERSION_FIELD,
        self::UPDATED_AT_FIELD,
    ];

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    protected function connection(): Connection
    {
        return $this->connection;
    }

    abstract protected function map(array $item): mixed;

    abstract protected static function tableName(): string;

    protected function mapCollection(array $collection): array
    {
        $items = [];

        foreach ($collection as $item) {
            $items[] = $this->map($item);
        }

        return $items;
    }

    protected function addControlFields(array $item, object $model): object
    {
        foreach (self::CONTROL_FIELDS as $field) {
            $model = $this->addField($item, $model, $field);
        }

        return $model;
    }

    private function addField(array $item, object $model, string $field): object
    {
        if (\array_key_exists($field, $item)) {
            $model->$field = $item[$field];
        }

        return $model;
    }

    protected function executeInsert(array $data): void
    {
        $this->connection()->insert(static::tableName(), $data);
    }

    protected function executeUpdate(mixed $model, array $data, array $criteria): void
    {
        \assert(is_object($model));

        $versionField = self::VERSION_FIELD;

        if (\property_exists($model, $versionField)) {
            $data[$versionField] = $model->$versionField + 1;
            $criteria[$versionField] = $model->$versionField;

            $model->$versionField = $model->$versionField + 1;
        }

        if (\property_exists($model, self::UPDATED_AT_FIELD)) {
            $now = DateTimeValueObject::now();

            $data[self::UPDATED_AT_FIELD] = $now->format('Y-m-d H:i:s.u');
            $model->{self::UPDATED_AT_FIELD} = $now;
        }

        $updatedRows = $this->connection()->update(static::tableName(), $data, $criteria);

        if (0 === $updatedRows) {
            throw TransactionLockFailedException::from(
                \get_class($model),
                $data,
                $criteria,
            );
        }
    }

    protected function executeDelete(array $criteria): void
    {
        $this->connection()->delete(static::tableName(), $criteria);
    }

    protected function findOnyByIdentification(string $table, string $value, string $field = 'id'): ?array
    {
        $result = $this->connection->createQueryBuilder()
            ->select(\sprintf('%s.*', $table))
            ->from($table, $table)
            ->where(\sprintf('%s.%s = :value', $table, $field))
            ->setParameter('value', $value)
            ->setMaxResults(1)
            ->fetchAssociative();

        if (false === $result) {
            return null;
        }

        return $result;
    }

    protected function findByCriteria(Criteria $criteria): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')->from(static::tableName(), 't');

        foreach ($criteria->filters() as $field => $filter) {
            $qb->andWhere("t.$field {$filter['operator']} :$field")
                ->setParameter((string) $field, $filter['value']);
        }

        if (null !== $criteria->limit()) {
            $qb->setMaxResults($criteria->limit());
        }

        if (null !== $criteria->offset()) {
            $qb->setFirstResult($criteria->offset());
        }

        if ($order = $criteria->order()) {
            $qb->orderBy('t.' . $order['field'], $order['direction']);
        }

        return $this->mapCollection($qb->executeQuery()->fetchAllAssociative());
    }

    public function countByCriteria(Criteria $criteria): int
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('COUNT(*) as total_count')
            ->from(static::tableName(), 't');

        foreach ($criteria->filters() as $field => $filter) {
            $qb->andWhere("t.$field {$filter['operator']} :$field")
                ->setParameter((string) $field, $filter['value']);
        }

        $result = $qb->executeQuery()->fetchAssociative();

        if (false === $result) {
            return 0;
        }

        \assert(\is_numeric($result['total_count']));

        return (int) $result['total_count'];
    }
}
