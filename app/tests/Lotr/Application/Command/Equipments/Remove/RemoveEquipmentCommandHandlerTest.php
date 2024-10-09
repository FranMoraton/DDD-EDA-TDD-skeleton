<?php

namespace App\Tests\Lotr\Application\Command\Equipments\Remove;

use App\Lotr\Application\Command\Equipments\Remove\RemoveEquipmentCommandHandler;
use App\Lotr\Domain\Model\Equipment\Event\EquipmentWasRemoved;
use App\Lotr\Domain\Model\Equipment\Equipment;
use App\Lotr\Domain\Model\Equipment\EquipmentRepository;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\NotFoundException;
use App\Tests\Lotr\Domain\Model\Equipment\RandomEquipmentGenerator;
use App\Tests\System\Infrastructure\PhpUnit\SpyTestHelper;
use PHPUnit\Framework\TestCase;

class RemoveEquipmentCommandHandlerTest extends TestCase
{
    use SpyTestHelper;

    private EquipmentRepository $equipmentRepository;
    private DomainEventPublisher $domainEventPublisher;

    public function testGivenRemoveWhenEquipmentDoesNotExistThenSuccess(): void
    {
        $command = RandomRemoveEquipmentCommand::execute();

        $this->equipmentRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(
                RandomEquipmentGenerator::execute($command->id()),
            );

        $this->equipmentRepository
            ->expects(self::once())
            ->method('remove');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($equipment),
            );

        $handler = new RemoveEquipmentCommandHandler(
            $this->equipmentRepository,
            $this->domainEventPublisher,
        );

        $handler($command);

        self::assertEquals($equipment::modelName(), Equipment::modelName());
        self::assertEquals($equipment->id(), $command->id());
        $firstEvent = $equipment->events()[array_key_first($equipment->events())];
        self::assertEquals($firstEvent::messageName(), EquipmentWasRemoved::messageName());
    }

    public function testGivenRemoveCommandWhenEquipmentDoesNotExistThenFail(): void
    {
        $command = RandomRemoveEquipmentCommand::execute();

        $this->equipmentRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(null);

        $this->equipmentRepository
            ->expects(self::never())
            ->method('remove');

        $this->domainEventPublisher
            ->expects(self::never())
            ->method('execute');

        $handler = new RemoveEquipmentCommandHandler(
            $this->equipmentRepository,
            $this->domainEventPublisher,
        );

        self::expectException(NotFoundException::class);
        $handler($command);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->equipmentRepository = $this->createMock(EquipmentRepository::class);
        $this->domainEventPublisher = $this->createMock(DomainEventPublisher::class);
    }
}
