<?php

declare(strict_types=1);

namespace App\Lotr\Domain\Model\Character;

use App\Lotr\Domain\Model\Character\Event\CharacterWasCreated;
use App\Lotr\Domain\Model\Character\Event\CharacterWasRemoved;
use App\Lotr\Domain\Model\Character\Event\CharacterWasUpdated;
use App\Lotr\Domain\Model\Character\ValueObject\BirthDate;
use App\Lotr\Domain\Model\Character\ValueObject\EquipmentId;
use App\Lotr\Domain\Model\Character\ValueObject\FactionId;
use App\Lotr\Domain\Model\Character\ValueObject\Id;
use App\Lotr\Domain\Model\Character\ValueObject\Kingdom;
use App\Lotr\Domain\Model\Character\ValueObject\Name;
use App\System\Domain\Model\Aggregate;

class Character extends Aggregate
{
    private const string NAME = 'character';

    private function __construct(
        private readonly Id $id,
        private readonly Name $name,
        private readonly BirthDate $birthDate,
        private readonly Kingdom $kingdom,
        private readonly EquipmentId $equipmentId,
        private readonly FactionId $factionId,
    ) {
        parent::__construct();
    }

    public static function from(
        string $id,
        string $name,
        string $birthDate,
        string $kingdom,
        string $equipmentId,
        string $factionId,
    ): self {
        return new self(
            Id::from($id),
            Name::from($name),
            BirthDate::from($birthDate),
            Kingdom::from($kingdom),
            EquipmentId::from($equipmentId),
            FactionId::from($factionId),
        );
    }

    public static function create(
        string $id,
        string $name,
        string $birthDate,
        string $kingdom,
        string $equipmentId,
        string $factionId,
    ): self {
        $character = new self(
            $idVo = Id::from($id),
            $nameVo = Name::from($name),
            $birthDateVo = BirthDate::from($birthDate),
            $kingdomVo = Kingdom::from($kingdom),
            $equipmentIdVo = EquipmentId::from($equipmentId),
            $factionIdVo = FactionId::from($factionId),
        );

        $character->recordThat(
            CharacterWasCreated::from(
                $idVo,
                $nameVo,
                $birthDateVo,
                $kingdomVo,
                $equipmentIdVo,
                $factionIdVo,
            )
        );

        return $character;
    }

    public function update(
        string $name,
        string $birthDate,
        string $kingdom,
        string $equipmentId,
        string $factionId,
    ): self {
            $nameVo = Name::from($name);
            $birthDateVo = BirthDate::from($birthDate);
            $kingdomVo = Kingdom::from($kingdom);
            $equipmentIdVo = EquipmentId::from($equipmentId);
            $factionIdVo = FactionId::from($factionId);

        if (
            true === $this->name->equalTo($nameVo)
            && true === $this->birthDate->equalTo($birthDateVo)
            && true === $this->kingdom->equalTo($kingdomVo)
            && true === $this->equipmentId->equalTo($equipmentIdVo)
            && true === $this->factionId->equalTo($factionIdVo)
        ) {
            return $this;
        }

        $character = new self(
            $idVo = $this->id,
            $nameVo,
            $birthDateVo,
            $kingdomVo,
            $equipmentIdVo,
            $factionIdVo,
        );

        $character->recordThat(
            CharacterWasUpdated::from(
                $idVo,
                $nameVo,
                $birthDateVo,
                $kingdomVo,
                $equipmentIdVo,
                $factionIdVo,
            )
        );

        return $character;
    }

    public function remove(): self
    {
        $this->recordThat(
            CharacterWasRemoved::from(
                $this->id,
                $this->name,
                $this->birthDate,
                $this->kingdom,
                $this->equipmentId,
                $this->factionId,
            )
        );

        return $this;
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function birthDate(): BirthDate
    {
        return $this->birthDate;
    }

    public function kingdom(): Kingdom
    {
        return $this->kingdom;
    }

    public function equipmentId(): EquipmentId
    {
        return $this->equipmentId;
    }

    public function factionId(): FactionId
    {
        return $this->factionId;
    }

    public static function modelName(): string
    {
        return self::NAME;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'birth_date' => $this->birthDate,
            'kingdom' => $this->kingdom,
            'equipment_id' => $this->equipmentId,
            'faction_id' => $this->factionId,
        ];
    }
}
