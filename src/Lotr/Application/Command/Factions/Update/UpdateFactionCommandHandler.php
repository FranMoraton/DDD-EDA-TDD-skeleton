<?php

namespace App\Lotr\Application\Command\Factions\Update;

use App\Lotr\Domain\Model\Faction\Faction;
use App\Lotr\Domain\Model\Faction\FactionRepository;
use App\Lotr\Domain\Model\Faction\ValueObject\Id;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\NotFoundException;

final readonly class UpdateFactionCommandHandler
{
    public function __construct(
        private FactionRepository $factionRepository,
        private DomainEventPublisher $domainEventPublisher,
    ) {
    }

    public function __invoke(UpdateFactionCommand $command): void
    {
        $faction = $this->factionRepository->byId(Id::from($command->id()));

        if (null === $faction) {
            throw new NotFoundException(Faction::modelName(), 'faction', ['id' => $faction]);
        }

        $faction = $faction->update(
            $command->name(),
            $command->description(),
        );

        $this->factionRepository->update($faction);

        $this->domainEventPublisher->execute($faction);
    }
}
