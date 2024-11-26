<?php

declare(strict_types=1);

namespace App\Lotr\Application\Command\Characters\Create;

use App\System\Application\Command;

final readonly class CreateCharacterCommand implements Command
{
    private const string NAME = 'company.lotr.1.command.character.create';

    public function __construct(
        private string $id,
        private string $name,
        private string $birthDate,
        private string $kingdom,
        private string $equipmentId,
        private string $factionId,
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function birthDate(): string
    {
        return $this->birthDate;
    }

    public function kingdom(): string
    {
        return $this->kingdom;
    }

    public function equipmentId(): string
    {
        return $this->equipmentId;
    }

    public function factionId(): string
    {
        return $this->factionId;
    }

    public static function messageName(): string
    {
        return self::NAME;
    }
}
