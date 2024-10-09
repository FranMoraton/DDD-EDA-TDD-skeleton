<?php

namespace App\Lotr\Application\Command\Equipments\Update;

use App\Lotr\Domain\Model\Equipment\Equipment;
use App\Lotr\Domain\Model\Equipment\EquipmentRepository;
use App\Lotr\Domain\Model\Equipment\ValueObject\Id;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\NotFoundException;

final readonly class UpdateEquipmentCommandHandler
{
    public function __construct(
        private EquipmentRepository $equipmentRepository,
        private DomainEventPublisher $domainEventPublisher,
    ) {
    }

    public function __invoke(UpdateEquipmentCommand $command): void
    {
        $equipment = $this->equipmentRepository->byId(Id::from($command->id()));

        if (null === $equipment) {
            throw new NotFoundException(Equipment::modelName(), Equipment::modelName(), ['id' => $equipment]);
        }

        $equipment = $equipment->update(
            $command->name(),
            $command->type(),
            $command->madeBy(),
        );

        $this->equipmentRepository->update($equipment);

        $this->domainEventPublisher->execute($equipment);
    }
}
