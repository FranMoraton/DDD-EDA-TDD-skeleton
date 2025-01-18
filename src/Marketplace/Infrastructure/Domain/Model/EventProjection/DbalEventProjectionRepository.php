<?php

declare(strict_types=1);

namespace App\Marketplace\Infrastructure\Domain\Model\EventProjection;

use App\Marketplace\Domain\Model\EventProjection\EventProjection;
use App\Marketplace\Domain\Model\EventProjection\EventProjectionRepository;
use App\System\Domain\Criteria\Criteria;
use Doctrine\DBAL\Connection;

class DbalEventProjectionRepository implements EventProjectionRepository
{
    private const string TABLE_NAME = 'events_projection';

    public function __construct(private readonly Connection $connection)
    {
    }

    protected function map(array $item): EventProjection
    {
        $model = DbalArrayEventProjectionMapper::map($item);
        \assert($model instanceof EventProjection);

        return $model;
    }

    protected function mapCollection(array $collection): array
    {
        $items = [];

        foreach ($collection as $item) {
            $items[] = $this->map($item);
        }

        return $items;
    }

    public function upsertByEventDate(EventProjection $eventProjection): void
    {
        $sql = "
            INSERT INTO " . self::TABLE_NAME . " (
                id,
                title,
                start_date,
                start_time,
                end_date,
                end_time,
                min_price,
                max_price,
                starts_at,
                ends_at,
                last_event_date
            )
            VALUES (
                :id,
                :title,
                :start_date,
                :start_time,
                :end_date,
                :end_time,
                :min_price,
                :max_price,
                :starts_at,
                :ends_at,
                :last_event_date
            )
            ON CONFLICT (id) 
            DO UPDATE 
            SET
                title = EXCLUDED.title,
                start_date = EXCLUDED.start_date,
                start_time = EXCLUDED.start_time,
                end_date = EXCLUDED.end_date,
                end_time = EXCLUDED.end_time,
                min_price = EXCLUDED.min_price,
                max_price = EXCLUDED.max_price,
                starts_at = EXCLUDED.starts_at,
                ends_at = EXCLUDED.ends_at,
                last_event_date = EXCLUDED.last_event_date
            WHERE " . self::TABLE_NAME . ".last_event_date < EXCLUDED.last_event_date;
        ";

        $this->connection->executeStatement(
            $sql,
            DbalArrayEventProjectionMapper::toArray($eventProjection),
        );
    }

    public function search(Criteria $criteria): array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select(
            '
                t.id,
                t.title,
                t.start_date,
                t.start_time,
                t.end_date,
                t.end_time,
                t.min_price,
                t.max_price,
                t.starts_at,
                t.ends_at,
                t.last_event_date
            '
        )->from(self::TABLE_NAME, 't');

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

    public function count(Criteria $criteria): int
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('COUNT(id) as total_count')
            ->from(self::TABLE_NAME, 't');

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
