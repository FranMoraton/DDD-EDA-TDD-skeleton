<?php

namespace App\Lotr\Application\Command\Equipments\Remove;

use App\Lotr\Domain\Model\Equipment\Equipment;
use App\Lotr\Domain\Model\Equipment\EquipmentRepository;
use App\Lotr\Domain\Model\Equipment\ValueObject\Id;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\NotFoundException;

final readonly class RemoveEquipmentCommandHandler
{
    public function __construct(
        private EquipmentRepository $equipmentRepository,
        private DomainEventPublisher $domainEventPublisher,
    ) {
    }

    public function __invoke(RemoveEquipmentCommand $command): void
    {
        $equipment = $this->equipmentRepository->byId(Id::from($command->id()));

        if (null === $equipment) {
            throw new NotFoundException(Equipment::modelName(), Equipment::modelName(), ['id' => $equipment]);
        }

        $equipment->remove();

        $this->equipmentRepository->remove($equipment);

        $this->domainEventPublisher->execute($equipment);
    }
}
