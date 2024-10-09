<?php

namespace App\Lotr\Application\Command\Equipments\Create;

use App\Lotr\Domain\Model\Equipment\Equipment;
use App\Lotr\Domain\Model\Equipment\EquipmentRepository;
use App\Lotr\Domain\Model\Equipment\ValueObject\Id;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\AlreadyExistException;

final readonly class CreateEquipmentCommandHandler
{
    public function __construct(
        private EquipmentRepository $equipmentRepository,
        private DomainEventPublisher $domainEventPublisher,
    ) {
    }

    public function __invoke(CreateEquipmentCommand $command): void
    {
        $equipment = $this->equipmentRepository->byId(Id::from($command->id()));

        if (null !== $equipment) {
            throw new AlreadyExistException(Equipment::modelName(), Equipment::modelName(), ['id' => $equipment]);
        }

        $equipment = Equipment::create(
            $command->id(),
            $command->name(),
            $command->type(),
            $command->madeBy(),
        );

        $this->equipmentRepository->add($equipment);

        $this->domainEventPublisher->execute($equipment);
    }
}
