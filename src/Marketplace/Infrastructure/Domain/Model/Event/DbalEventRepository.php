<?php

declare(strict_types=1);

namespace App\Marketplace\Infrastructure\Domain\Model\Event;

use App\Marketplace\Domain\Model\Event\Event;
use App\Marketplace\Domain\Model\Event\EventRepository;
use App\Marketplace\Domain\Model\Event\ValueObject\Id;
use App\System\Domain\Criteria\Criteria;
use App\System\Infrastructure\Dbal\DbalRepository;

class DbalEventRepository extends DbalRepository implements EventRepository
{
    private const string TABLE_NAME = 'events';

    public function byId(Id $id): ?Event
    {
        $result = $this->findOnyByIdentification(
            self::TABLE_NAME,
            $id->value(),
            'id'
        );

        return null !== $result
            ? $this->map($result)
            : null;
    }

    public function add(Event $event): void
    {
        $this->executeInsert(DbalArrayEventMapper::toArray($event));
    }

    public function update(Event $event): void
    {
        $this->executeUpdate(
            $event,
            DbalArrayEventMapper::toArray($event),
            [
                'id' => $event->id(),
            ],
        );
    }

    public function remove(Event $event): void
    {
        $this->executeDelete(['id' => $event->id()]);
    }

    public function search(Criteria $criteria): array
    {
        return $this->findByCriteria($criteria);
    }

    public function count(Criteria $criteria): int
    {
        return $this->countByCriteria($criteria);
    }

    protected static function tableName(): string
    {
        return self::TABLE_NAME;
    }

    protected function map(array $item): Event
    {
        $model = $this->addControlFields($item, DbalArrayEventMapper::map($item));
        \assert($model instanceof Event);

        return $model;
    }
}
