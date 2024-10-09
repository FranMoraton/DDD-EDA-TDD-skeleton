<?php

declare(strict_types=1);

namespace App\Lotr\Domain\Model\Equipment;

use App\Lotr\Domain\Model\Equipment\Event\EquipmentWasCreated;
use App\Lotr\Domain\Model\Equipment\Event\EquipmentWasRemoved;
use App\Lotr\Domain\Model\Equipment\Event\EquipmentWasUpdated;
use App\Lotr\Domain\Model\Equipment\ValueObject\Id;
use App\Lotr\Domain\Model\Equipment\ValueObject\MadeBy;
use App\Lotr\Domain\Model\Equipment\ValueObject\Name;
use App\Lotr\Domain\Model\Equipment\ValueObject\Type;
use App\System\Domain\Model\Aggregate;

class Equipment extends Aggregate
{
    private const string NAME = 'equipment';

    private function __construct(
        private Id $id,
        private Name $name,
        private Type $type,
        private MadeBy $madeBy,
    ) {
        parent::__construct();
    }

    public static function from(
        string $id,
        string $name,
        string $type,
        string $madeBy,
    ): self {
        return new self(
            Id::from($id),
            Name::from($name),
            Type::from($type),
            MadeBy::from($madeBy),
        );
    }

    public static function create(
        string $id,
        string $name,
        string $type,
        string $madeBy,
    ): self {
        $equipment = new self(
            $idVo = Id::from($id),
            $nameVo = Name::from($name),
            $typeVo = Type::from($type),
            $madeByVo = MadeBy::from($madeBy),
        );

        $equipment->recordThat(EquipmentWasCreated::from($idVo, $nameVo, $typeVo, $madeByVo));

        return $equipment;
    }

    public function update(
        string $name,
        string $type,
        string $madeBy,
    ): self {
        $nameVo = Name::from($name);
        $typeVo = Type::from($type);
        $madeByVo = MadeBy::from($madeBy);

        if (
            true === $this->name->equalTo($nameVo)
            && true === $this->type->equalTo($typeVo)
            && true === $this->madeBy->equalTo($madeByVo)
        ) {
            return $this;
        }

        $equipment = new self(
            $idVo = $this->id,
            $nameVo,
            $typeVo,
            $madeByVo,
        );

        $equipment->recordThat(EquipmentWasUpdated::from($idVo, $nameVo, $typeVo, $madeByVo));

        return $equipment;
    }

    public function remove(): self
    {
        $this->recordThat(EquipmentWasRemoved::from($this->id, $this->name, $this->type, $this->madeBy));

        return $this;
    }


    public static function modelName(): string
    {
        return self::NAME;
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function madeBy(): MadeBy
    {
        return $this->madeBy;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'made_by' => $this->madeBy,
        ];
    }
}
