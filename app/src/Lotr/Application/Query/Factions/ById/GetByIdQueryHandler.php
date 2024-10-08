<?php

declare(strict_types=1);

namespace App\Lotr\Application\Query\Factions\ById;

use App\Lotr\Domain\Model\Faction\Faction;
use App\Lotr\Domain\Model\Faction\FactionRepository;
use App\Lotr\Domain\Model\Faction\ValueObject\Id;

final readonly class GetByIdQueryHandler
{
    public function __construct(private FactionRepository $factionRepository)
    {
    }

    public function __invoke(GetByIdQuery $query): ?Faction
    {
        return $this->factionRepository->byId(Id::from($query->id()));
    }
}
