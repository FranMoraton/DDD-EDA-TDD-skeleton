<?php

declare(strict_types=1);

namespace App\Lotr\Domain\Model\Faction;

use App\Lotr\Domain\Model\Faction\Event\FactionWasCreated;
use App\Lotr\Domain\Model\Faction\ValueObject\Description;
use App\Lotr\Domain\Model\Faction\ValueObject\Id;
use App\Lotr\Domain\Model\Faction\ValueObject\Name;
use App\System\Domain\Model\Aggregate;

class Faction extends Aggregate
{
    private const string NAME = 'faction';

    private function __construct(
        private Id $id,
        private Name $name,
        private Description $description,
    ) {
        parent::__construct();
    }

    public static function from(
        string $id,
        string $name,
        string $description,
    ): self {
        return new self(
            Id::from($id),
            Name::from($name),
            Description::from($description),
        );
    }

    public static function create(
        string $id,
        string $name,
        string $description,
    ): self {
        $faction = new self(
            $idVo = Id::from($id),
            $nameVo = Name::from($name),
            $descriptionVo = Description::from($description),
        );

        $faction->recordThat(FactionWasCreated::from($idVo, $nameVo, $descriptionVo));

        return $faction;
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

    public function description(): Description
    {
        return $this->description;
    }
}
