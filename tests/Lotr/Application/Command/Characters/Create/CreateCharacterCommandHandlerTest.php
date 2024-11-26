<?php

namespace App\Tests\Lotr\Application\Command\Characters\Create;

use App\Lotr\Application\Command\Characters\Create\CreateCharacterCommandHandler;
use App\Lotr\Domain\Model\Character\Character;
use App\Lotr\Domain\Model\Character\CharacterRepository;
use App\Lotr\Domain\Model\Character\Event\CharacterWasCreated;
use App\Lotr\Domain\Model\Equipment\EquipmentRepository;
use App\Lotr\Domain\Model\Faction\FactionRepository;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\AlreadyExistException;
use App\System\Domain\Exception\NotFoundException;
use App\Tests\Lotr\Domain\Model\Character\RandomCharacterGenerator;
use App\Tests\Lotr\Domain\Model\Equipment\RandomEquipmentGenerator;
use App\Tests\Lotr\Domain\Model\Faction\RandomFactionGenerator;
use App\Tests\System\Infrastructure\PhpUnit\SpyTestHelper;
use PHPUnit\Framework\TestCase;

class CreateCharacterCommandHandlerTest extends TestCase
{
    use SpyTestHelper;

    private FactionRepository $factionRepository;
    private EquipmentRepository $equipmentRepository;
    private CharacterRepository $characterRepository;
    private DomainEventPublisher $domainEventPublisher;

    private CreateCharacterCommandHandler $handler;

    public function testGivenCreateCommandWhenEquipmentExistAndFactionDoesNotExistThenFail(): void
    {
        $command = RandomCreateCharacterCommand::execute();

        $this->equipmentRepository
            ->expects(self::once())
            ->method('byId')
            ->willReturn(
                RandomEquipmentGenerator::execute(id: $command->equipmentId())
            );

        $this->factionRepository
            ->expects(self::once())
            ->method('byId')
            ->willReturn(
                null
            );

        $this->characterRepository
            ->expects(self::never())
            ->method('byId')
            ->willReturn(
                null
            );

        $this->characterRepository
            ->expects(self::never())
            ->method('add');

        $this->domainEventPublisher
            ->expects(self::never())
            ->method('execute')
            ->will(
                self::extractArguments($character),
            );

        $this->expectException(NotFoundException::class);
        ($this->handler)($command);
    }

    public function testGivenCreateCommandWhenEquipmentDoesNotExistThenFail(): void
    {
        $command = RandomCreateCharacterCommand::execute();

        $this->equipmentRepository
            ->expects(self::once())
            ->method('byId')
            ->willReturn(
                null
            );

        $this->factionRepository
            ->expects(self::never())
            ->method('byId')
            ->willReturn(
                RandomFactionGenerator::execute(id: $command->factionId())
            );

        $this->characterRepository
            ->expects(self::never())
            ->method('byId')
            ->willReturn(
                null
            );

        $this->characterRepository
            ->expects(self::never())
            ->method('add');

        $this->domainEventPublisher
            ->expects(self::never())
            ->method('execute')
            ->will(
                self::extractArguments($character),
            );

        $this->expectException(NotFoundException::class);
        ($this->handler)($command);
    }

    public function testGivenCreateCommandWhenCharacterAlreadyExistThenFail(): void
    {
        $command = RandomCreateCharacterCommand::execute();

        $this->equipmentRepository
            ->expects(self::once())
            ->method('byId')
            ->willReturn(
                RandomEquipmentGenerator::execute(id: $command->equipmentId())
            );

        $this->factionRepository
            ->expects(self::once())
            ->method('byId')
            ->willReturn(
                RandomFactionGenerator::execute(id: $command->factionId())
            );

        $this->characterRepository
            ->expects(self::once())
            ->method('byId')
            ->willReturn(
                RandomCharacterGenerator::execute()
            );

        $this->characterRepository
            ->expects(self::never())
            ->method('add');

        $this->domainEventPublisher
            ->expects(self::never())
            ->method('execute')
            ->will(
                self::extractArguments($character),
            );

        $this->expectException(AlreadyExistException::class);
        ($this->handler)($command);
    }

    public function testGivenCreateCommandWhenHappyPathThenSuccess(): void
    {
        $command = RandomCreateCharacterCommand::execute();

        $this->equipmentRepository
            ->expects(self::once())
            ->method('byId')
            ->willReturn(
                RandomEquipmentGenerator::execute(id: $command->equipmentId())
            );

        $this->factionRepository
            ->expects(self::once())
            ->method('byId')
            ->willReturn(
                RandomFactionGenerator::execute(id: $command->factionId())
            );

        $this->characterRepository
            ->expects(self::once())
            ->method('byId')
            ->willReturn(null);

        $this->characterRepository
            ->expects(self::once())
            ->method('add');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($character),
            );

        ($this->handler)($command);

        self::assertEquals($character::modelName(), Character::modelName());
        self::assertEquals($character->id(), $command->id());
        self::assertEquals($character->name(), $command->name());
        self::assertEquals($character->birthDate()->toDate(), $command->birthDate());
        self::assertEquals($character->kingdom(), $command->kingdom());
        self::assertEquals($character->equipmentId(), $command->equipmentId());
        self::assertEquals($character->factionId(), $command->factionId());

        $firstEvent = $character->events()[array_key_first($character->events())];
        self::assertEquals($firstEvent::messageName(), CharacterWasCreated::messageName());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->equipmentRepository = $this->createMock(EquipmentRepository::class);
        $this->factionRepository = $this->createMock(FactionRepository::class);
        $this->characterRepository = $this->createMock(CharacterRepository::class);
        $this->domainEventPublisher = $this->createMock(DomainEventPublisher::class);

        $this->handler = new CreateCharacterCommandHandler(
            $this->equipmentRepository,
            $this->factionRepository,
            $this->characterRepository,
            $this->domainEventPublisher,
        );
    }
}
