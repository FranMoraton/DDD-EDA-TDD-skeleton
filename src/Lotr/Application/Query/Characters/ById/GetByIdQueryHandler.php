<?php

declare(strict_types=1);

namespace App\Lotr\Application\Query\Characters\ById;

use App\Lotr\Domain\Model\Character\Character;
use App\Lotr\Domain\Model\Character\CharacterRepository;
use App\Lotr\Domain\Model\Character\ValueObject\Id;

final readonly class GetByIdQueryHandler
{
    public function __construct(private CharacterRepository $characterRepository)
    {
    }

    public function __invoke(GetByIdQuery $query): ?Character
    {
        return $this->characterRepository->byId(Id::from($query->id()));
    }
}
