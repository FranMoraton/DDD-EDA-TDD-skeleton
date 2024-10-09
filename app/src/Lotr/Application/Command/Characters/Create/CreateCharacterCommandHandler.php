<?php

namespace App\Lotr\Application\Command\Characters\Create;

use App\Lotr\Domain\Model\Character\Character;
use App\Lotr\Domain\Model\Character\CharacterRepository;
use App\Lotr\Domain\Model\Equipment\Equipment;
use App\Lotr\Domain\Model\Character\ValueObject\Id;
use App\Lotr\Domain\Model\Equipment\EquipmentRepository;
use App\Lotr\Domain\Model\Equipment\ValueObject\Id as EquipmentId;
use App\Lotr\Domain\Model\Faction\FactionRepository;
use App\Lotr\Domain\Model\Faction\ValueObject\Id as FactionId;
use App\Lotr\Domain\Model\Faction\Faction;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\AlreadyExistException;
use App\System\Domain\Exception\NotFoundException;

final readonly class CreateCharacterCommandHandler
{
    public function __construct(
        private EquipmentRepository $equipmentRepository,
        private FactionRepository $factionRepository,
        private CharacterRepository $characterRepository,
        private DomainEventPublisher $domainEventPublisher,
    ) {
    }

    public function __invoke(CreateCharacterCommand $command): void
    {
        $this->assertEquipmentExists($command);
        $this->assertFactionExists($command);
        $this->assertThatCharacterDoesNotExist($command);

        $character = Character::create(
            $command->id(),
            $command->name(),
            $command->birthDate(),
            $command->kingdom(),
            $command->equipmentId(),
            $command->factionId(),
        );

        $this->characterRepository->add($character);

        $this->domainEventPublisher->execute($character);
    }

    public function assertEquipmentExists(CreateCharacterCommand $command): void
    {
        $equipment = $this->equipmentRepository->byId(EquipmentId::from($command->equipmentId()));

        if (null === $equipment) {
            throw new NotFoundException(Equipment::modelName(), Equipment::modelName(), ['id' => $command->equipmentId()]);
        }
    }

    public function assertFactionExists(CreateCharacterCommand $command): void
    {
        $faction = $this->factionRepository->byId(FactionId::from($command->factionId()));

        if (null === $faction) {
            throw new NotFoundException(Faction::modelName(), Faction::modelName(), ['id' => $command->factionId()]);
        }
    }

    public function assertThatCharacterDoesNotExist(CreateCharacterCommand $command): void
    {
        $character = $this->characterRepository->byId(Id::from($command->factionId()));

        if (null !== $character) {
            throw new AlreadyExistException(Character::modelName(), Character::modelName(), ['id' => $command->id()]);
        }
    }
}
