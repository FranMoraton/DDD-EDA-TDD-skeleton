<?php

declare(strict_types=1);

namespace App\Lotr\Infrastructure\Domain\Model\Faction;

use App\Lotr\Domain\Model\Faction\Faction;
use App\Lotr\Domain\Model\Faction\FactionRepository;
use App\Lotr\Domain\Model\Faction\ValueObject\Id;
use App\System\Infrastructure\Dbal\DbalRepository;

class DbalFactionRepository extends DbalRepository implements FactionRepository
{
    private const string TABLE_NAME = 'factions';

    public function byId(Id $id): ?Faction
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

    public function add(Faction $faction): void
    {
        $this->executeInsert(DbalArrayFactionMapper::toArray($faction));
    }

    public function update(Faction $faction): void
    {
        $this->executeUpdate(
            $faction,
            DbalArrayFactionMapper::toArray($faction),
            [
                'id' => $faction->id(),
            ],
        );
    }

    protected static function tableName(): string
    {
        return self::TABLE_NAME;
    }

    protected function map(array $item): Faction
    {
        $model = $this->addControlFields($item, DbalArrayFactionMapper::map($item));
        \assert($model instanceof Faction);

        return $model;
    }
}
