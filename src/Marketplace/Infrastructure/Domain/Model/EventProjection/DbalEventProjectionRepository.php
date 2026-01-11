<?php

declare(strict_types=1);

namespace App\Marketplace\Infrastructure\Domain\Model\EventProjection;

use App\Marketplace\Domain\Model\EventProjection\EventProjection;
use App\Marketplace\Domain\Model\EventProjection\EventProjectionRepository;
use App\System\Domain\Criteria\Criteria;
use App\System\Infrastructure\Dbal\DbalRepository;
use Doctrine\DBAL\Connection;

class DbalEventProjectionRepository extends DbalRepository implements EventProjectionRepository
{
    private const string TABLE_NAME = 'events_projection';

    public function __construct(private readonly Connection $connection)
    {
        parent::__construct($this->connection);
    }

    public static function tableName(): string
    {
        return self::TABLE_NAME;
    }

    protected function map(array $item): EventProjection
    {
        return DbalArrayEventProjectionMapper::map($item);
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
        return $this->findByCriteria($criteria);
    }

    public function count(Criteria $criteria): int
    {
        return $this->countByCriteria($criteria);
    }
}
