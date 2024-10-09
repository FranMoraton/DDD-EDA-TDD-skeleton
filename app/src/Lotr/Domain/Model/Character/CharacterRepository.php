<?php

declare(strict_types=1);

namespace App\Lotr\Domain\Model\Character;

use App\Lotr\Domain\Model\Character\ValueObject\Id;

interface CharacterRepository
{
    public function byId(Id $id): ?Character;
    public function add(Character $character): void;
    public function update(Character $character): void;
    public function remove(Character $character): void;
}
