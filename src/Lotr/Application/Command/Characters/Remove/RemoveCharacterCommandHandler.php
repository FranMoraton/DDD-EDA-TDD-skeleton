<?php

namespace App\Lotr\Application\Command\Characters\Remove;

use App\Lotr\Domain\Model\Character\Character;
use App\Lotr\Domain\Model\Character\CharacterRepository;
use App\Lotr\Domain\Model\Character\ValueObject\Id;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\NotFoundException;

final readonly class RemoveCharacterCommandHandler
{
    public function __construct(
        private CharacterRepository $characterRepository,
        private DomainEventPublisher $domainEventPublisher,
    ) {
    }

    public function __invoke(RemoveCharacterCommand $command): void
    {
        $equipment = $this->characterRepository->byId(Id::from($command->id()));

        if (null === $equipment) {
            throw new NotFoundException(Character::modelName(), Character::modelName(), ['id' => $equipment]);
        }

        $equipment->remove();

        $this->characterRepository->remove($equipment);

        $this->domainEventPublisher->execute($equipment);
    }
}
