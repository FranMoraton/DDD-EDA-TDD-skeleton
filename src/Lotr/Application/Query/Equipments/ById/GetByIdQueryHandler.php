<?php

declare(strict_types=1);

namespace App\Lotr\Application\Query\Equipments\ById;

use App\Lotr\Domain\Model\Equipment\Equipment;
use App\Lotr\Domain\Model\Equipment\EquipmentRepository;
use App\Lotr\Domain\Model\Equipment\ValueObject\Id;

final readonly class GetByIdQueryHandler
{
    public function __construct(private EquipmentRepository $factionRepository)
    {
    }

    public function __invoke(GetByIdQuery $query): ?Equipment
    {
        return $this->factionRepository->byId(Id::from($query->id()));
    }
}
