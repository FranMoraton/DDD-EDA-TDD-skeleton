<?php

namespace App\Tests\Lotr\Application\Command\Equipments\Create;

use App\Lotr\Application\Command\Equipments\Create\CreateEquipmentCommandHandler;
use App\Lotr\Domain\Model\Equipment\Event\EquipmentWasCreated;
use App\Lotr\Domain\Model\Equipment\Equipment;
use App\Lotr\Domain\Model\Equipment\EquipmentRepository;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\AlreadyExistException;
use App\Tests\Lotr\Domain\Model\Equipment\RandomEquipmentGenerator;
use App\Tests\System\Infrastructure\PhpUnit\SpyTestHelper;
use PHPUnit\Framework\TestCase;

class CreateEquipmentCommandHandlerTest extends TestCase
{
    use SpyTestHelper;

    private EquipmentRepository $equipmentRepository;
    private DomainEventPublisher $domainEventPublisher;

    public function testGivenCreateWhenEquipmentDoesNotExistThenSuccess(): void
    {
        $command = RandomCreateEquipmentCommand::execute();

        $this->equipmentRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(
                RandomEquipmentGenerator::execute(),
            );

        $this->equipmentRepository
            ->expects(self::never())
            ->method('add');

        $this->domainEventPublisher
            ->expects(self::never())
            ->method('execute');

        $handler = new CreateEquipmentCommandHandler(
            $this->equipmentRepository,
            $this->domainEventPublisher,
        );

        self::expectException(AlreadyExistException::class);
        $handler($command);
    }

    public function testGivenCreateCommandWhenEquipmentExistThenFail(): void
    {
        $command = RandomCreateEquipmentCommand::execute();

        $this->equipmentRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            );

        $this->equipmentRepository
            ->expects(self::once())
            ->method('add');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($equipment),
            );

        $handler = new CreateEquipmentCommandHandler(
            $this->equipmentRepository,
            $this->domainEventPublisher,
        );

        $handler($command);

        self::assertEquals($equipment::modelName(), Equipment::modelName());
        self::assertEquals($equipment->id(), $command->id());
        self::assertEquals($equipment->name(), $command->name());
        self::assertEquals($equipment->type(), $command->type());
        self::assertEquals($equipment->madeBy(), $command->madeBy());
        $firstEvent = $equipment->events()[array_key_first($equipment->events())];
        self::assertEquals($firstEvent::messageName(), EquipmentWasCreated::messageName());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->equipmentRepository = $this->createMock(EquipmentRepository::class);
        $this->domainEventPublisher = $this->createMock(DomainEventPublisher::class);
    }
}
