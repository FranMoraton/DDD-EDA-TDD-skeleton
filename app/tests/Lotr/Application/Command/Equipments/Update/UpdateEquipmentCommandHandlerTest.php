<?php

namespace App\Tests\Lotr\Application\Command\Equipments\Update;

use App\Lotr\Application\Command\Equipments\Update\UpdateEquipmentCommandHandler;
use App\Lotr\Domain\Model\Equipment\Event\EquipmentWasUpdated;
use App\Lotr\Domain\Model\Equipment\Equipment;
use App\Lotr\Domain\Model\Equipment\EquipmentRepository;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\NotFoundException;
use App\Tests\Lotr\Domain\Model\Equipment\RandomEquipmentGenerator;
use App\Tests\System\Infrastructure\PhpUnit\SpyTestHelper;
use PHPUnit\Framework\TestCase;

class UpdateEquipmentCommandHandlerTest extends TestCase
{
    use SpyTestHelper;

    private EquipmentRepository $equipmentRepository;
    private DomainEventPublisher $domainEventPublisher;

    public function testGivenUpdateWhenEquipmentDoesNotExistThenFail(): void
    {
        $command = RandomUpdateEquipmentCommand::execute();

        $this->equipmentRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(null);

        $this->equipmentRepository
            ->expects(self::never())
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::never())
            ->method('execute');

        $handler = new UpdateEquipmentCommandHandler(
            $this->equipmentRepository,
            $this->domainEventPublisher,
        );

        self::expectException(NotFoundException::class);
        $handler($command);
    }

    public function testGivenUpdateCommandWhenEquipmentAlreadyUpdatedThenDoNotUpdate(): void
    {
        $command = RandomUpdateEquipmentCommand::execute();

        $this->equipmentRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(
                $oldEquipment = RandomEquipmentGenerator::execute(
                    $command->id(),
                    $command->name(),
                    $command->type(),
                    $command->madeBy(),
                ),
            );

        $this->equipmentRepository
            ->expects(self::once())
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($equipment),
            );

        $handler = new UpdateEquipmentCommandHandler(
            $this->equipmentRepository,
            $this->domainEventPublisher,
        );

        $handler($command);

        self::assertEquals($equipment::modelName(), Equipment::modelName());
        self::assertEquals($equipment->id(), $command->id());
        self::assertEquals($equipment->name(), $command->name());
        self::assertEquals($equipment->type(), $command->type());
        self::assertEquals($equipment->madeBy(), $command->madeBy());

        self::assertCount(0, $equipment->events());
    }

    public function testGivenUpdateCommandWhenEquipmentTypeIsDifferentThenUpdate(): void
    {
        $command = RandomUpdateEquipmentCommand::execute();

        $this->equipmentRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(
                $oldEquipment = RandomEquipmentGenerator::execute(
                    $command->id(),
                    $command->name(),
                    madeBy: $command->madeBy(),
                ),
            );

        $this->equipmentRepository
            ->expects(self::once())
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($equipment),
            );

        $handler = new UpdateEquipmentCommandHandler(
            $this->equipmentRepository,
            $this->domainEventPublisher,
        );

        $handler($command);

        self::assertEquals($equipment::modelName(), Equipment::modelName());
        self::assertEquals($equipment->id(), $command->id());
        self::assertEquals($equipment->name(), $command->name());
        self::assertEquals($equipment->madeBy(), $command->madeBy());

        self::assertEquals($equipment->type(), $command->type());
        self::assertNotEquals($equipment->type(), $oldEquipment->type());

        $firstEvent = $equipment->events()[array_key_first($equipment->events())];
        self::assertEquals($firstEvent::messageName(), EquipmentWasUpdated::messageName());
    }

    public function testGivenUpdateCommandWhenEquipmentNameIsDifferentThenUpdate(): void
    {
        $command = RandomUpdateEquipmentCommand::execute();

        $this->equipmentRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(
                $oldEquipment = RandomEquipmentGenerator::execute(
                    $command->id(),
                    type: $command->type(),
                    madeBy: $command->madeBy(),
                ),
            );

        $this->equipmentRepository
            ->expects(self::once())
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($faction),
            );

        $handler = new UpdateEquipmentCommandHandler(
            $this->equipmentRepository,
            $this->domainEventPublisher,
        );

        $handler($command);

        self::assertEquals($faction::modelName(), Equipment::modelName());
        self::assertEquals($faction->id(), $command->id());
        self::assertEquals($faction->type(), $command->type());
        self::assertEquals($faction->madeBy(), $command->madeBy());

        self::assertEquals($faction->name(), $command->name());
        self::assertNotEquals($faction->name(), $oldEquipment->name());

        $firstEvent = $faction->events()[array_key_first($faction->events())];
        self::assertEquals($firstEvent::messageName(), EquipmentWasUpdated::messageName());
    }

    public function testGivenUpdateCommandWhenEquipmentMadeByIsDifferentThenUpdate(): void
    {
        $command = RandomUpdateEquipmentCommand::execute();

        $this->equipmentRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(
                $oldEquipment = RandomEquipmentGenerator::execute(
                    $command->id(),
                    name: $command->name(),
                    type: $command->type(),
                ),
            );

        $this->equipmentRepository
            ->expects(self::once())
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($faction),
            );

        $handler = new UpdateEquipmentCommandHandler(
            $this->equipmentRepository,
            $this->domainEventPublisher,
        );

        $handler($command);

        self::assertEquals($faction::modelName(), Equipment::modelName());
        self::assertEquals($faction->id(), $command->id());
        self::assertEquals($faction->type(), $command->type());
        self::assertEquals($faction->type(), $command->type());
        self::assertEquals($faction->name(), $command->name());

        self::assertEquals($faction->madeBy(), $command->madeBy());
        self::assertNotEquals($faction->madeBy(), $oldEquipment->madeBy());

        $firstEvent = $faction->events()[array_key_first($faction->events())];
        self::assertEquals($firstEvent::messageName(), EquipmentWasUpdated::messageName());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->equipmentRepository = $this->createMock(EquipmentRepository::class);
        $this->domainEventPublisher = $this->createMock(DomainEventPublisher::class);
    }
}
