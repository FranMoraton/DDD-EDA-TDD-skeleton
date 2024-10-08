<?php

namespace App\Lotr\Application\Command\Factions\Create;

use App\Lotr\Domain\Model\Faction\Faction;
use App\Lotr\Domain\Model\Faction\FactionRepository;
use App\Lotr\Domain\Model\Faction\ValueObject\Id;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\AlreadyExistException;

final readonly class CreateFactionCommandHandler
{
    public function __construct(
        private FactionRepository $factionRepository,
        private DomainEventPublisher $domainEventPublisher,
    ) {
    }

    public function __invoke(CreateFactionCommand $command): void
    {
        $faction = $this->factionRepository->byId(Id::from($command->id()));

        if (null !== $faction) {
            throw new AlreadyExistException(Faction::modelName(), 'faction', ['id' => $faction]);
        }

        $faction = Faction::create(
            $command->id(),
            $command->name(),
            $command->description(),
        );

        $this->factionRepository->add($faction);

        $this->domainEventPublisher->execute($faction);
    }
}