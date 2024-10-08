<?php

declare(strict_types=1);

namespace App\Lotr\Domain\Model\Faction;

use App\Lotr\Domain\Model\Faction\ValueObject\Id;

interface FactionRepository
{
    public function byId(Id $id): ?Faction;
    public function add(Faction $faction): void;
    public function update(Faction $faction): void;
}
