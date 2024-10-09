<?php

declare(strict_types=1);

namespace App\Lotr\Infrastructure\Domain\Model\Character;

use App\Lotr\Domain\Model\Character\Character;
use App\Lotr\Domain\Model\Character\CharacterRepository;
use App\Lotr\Domain\Model\Character\ValueObject\Id;
use App\System\Infrastructure\Dbal\DbalRepository;

class DbalCharacterRepository extends DbalRepository implements CharacterRepository
{
    private const string TABLE_NAME = 'characters';

    public function byId(Id $id): ?Character
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

    public function add(Character $character): void
    {
        $this->executeInsert(DbalArrayCharacterMapper::toArray($character));
    }

    public function update(Character $character): void
    {
        $this->executeUpdate(
            $character,
            DbalArrayCharacterMapper::toArray($character),
            [
                'id' => $character->id(),
            ],
        );
    }

    public function remove(Character $character): void
    {
        $this->executeDelete(['id' => $character->id()]);
    }

    protected static function tableName(): string
    {
        return self::TABLE_NAME;
    }

    protected function map(array $item): Character
    {
        $model = $this->addControlFields($item, DbalArrayCharacterMapper::map($item));
        \assert($model instanceof Character);

        return $model;
    }
}
