<?php

namespace App\Tests\Lotr\Application\Command\Characters\Update;

use App\Lotr\Application\Command\Characters\Update\UpdateCharacterCommandHandler;
use App\Lotr\Domain\Model\Character\Character;
use App\Lotr\Domain\Model\Character\CharacterRepository;
use App\Lotr\Domain\Model\Character\Event\CharacterWasUpdated;
use App\Lotr\Domain\Model\Equipment\EquipmentRepository;
use App\Lotr\Domain\Model\Faction\FactionRepository;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\NotFoundException;
use App\Tests\Lotr\Domain\Model\Character\RandomCharacterGenerator;
use App\Tests\Lotr\Domain\Model\Equipment\RandomEquipmentGenerator;
use App\Tests\Lotr\Domain\Model\Faction\RandomFactionGenerator;
use App\Tests\System\Infrastructure\PhpUnit\SpyTestHelper;
use PHPUnit\Framework\TestCase;

class UpdateCharacterCommandHandlerTest extends TestCase
{
    use SpyTestHelper;

    private FactionRepository $factionRepository;
    private EquipmentRepository $equipmentRepository;
    private CharacterRepository $characterRepository;
    private DomainEventPublisher $domainEventPublisher;

    private UpdateCharacterCommandHandler $handler;

    public function testGivenUpdateCommandWhenFactionDoesNotExistThenFail(): void
    {
        $command = RandomUpdateCharacterCommand::execute();

        $this->characterRepository
            ->expects(self::once())
            ->method('byId')
            ->willReturn(
                $oldCharacter = RandomCharacterGenerator::execute()
            );

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
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::never())
            ->method('execute')
            ->will(
                self::extractArguments($character),
            );

        $this->expectException(NotFoundException::class);
        ($this->handler)($command);
    }

    public function testGivenUpdateCommandWhenEquipmentDoesNotExistThenFail(): void
    {
        $command = RandomUpdateCharacterCommand::execute();

        $this->characterRepository
            ->expects(self::once())
            ->method('byId')
            ->willReturn(
                $oldCharacter = RandomCharacterGenerator::execute()
            );

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
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::never())
            ->method('execute')
            ->will(
                self::extractArguments($character),
            );

        $this->expectException(NotFoundException::class);
        ($this->handler)($command);
    }

    public function testGivenUpdateCommandWhenCharacterDoesNotExistThenFail(): void
    {
        $command = RandomUpdateCharacterCommand::execute();

        $this->characterRepository
            ->expects(self::once())
            ->method('byId')
            ->willReturn(
                null
            );

        $this->equipmentRepository
            ->expects(self::never())
            ->method('byId')
            ->willReturn(
                RandomEquipmentGenerator::execute(id: $command->equipmentId())
            );

        $this->factionRepository
            ->expects(self::never())
            ->method('byId')
            ->willReturn(
                RandomFactionGenerator::execute(id: $command->factionId())
            );

        $this->characterRepository
            ->expects(self::never())
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::never())
            ->method('execute')
            ->will(
                self::extractArguments($character),
            );

        $this->expectException(NotFoundException::class);
        ($this->handler)($command);
    }

    public function testGivenUpdateCommandWhenHappyPathThenSuccess(): void
    {
        $command = RandomUpdateCharacterCommand::execute();

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
                $oldCharacter = RandomCharacterGenerator::execute()
            );

        $this->characterRepository
            ->expects(self::once())
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($character),
            );

        ($this->handler)($command);

        self::assertEquals($character::modelName(), Character::modelName());

        self::assertEquals($character->id(), $oldCharacter->id());
        self::assertEquals($character->name(), $command->name());
        self::assertEquals($character->birthDate()->toDate(), $command->birthDate());
        self::assertEquals($character->kingdom(), $command->kingdom());
        self::assertEquals($character->equipmentId(), $command->equipmentId());
        self::assertEquals($character->factionId(), $command->factionId());

        self::assertNotEquals($character->name(), $oldCharacter->name());
        self::assertNotEquals($character->birthDate()->toDate(), $oldCharacter->birthDate()->toDate());
        self::assertNotEquals($character->kingdom(), $oldCharacter->kingdom());
        self::assertNotEquals($character->equipmentId(), $oldCharacter->equipmentId());
        self::assertNotEquals($character->factionId(), $oldCharacter->factionId());

        $firstEvent = $character->events()[array_key_first($character->events())];
        self::assertEquals($firstEvent::messageName(), CharacterWasUpdated::messageName());
    }

    public function testGivenUpdateCommandWhenSameItemsThenDoNotUpdate(): void
    {
        $command = RandomUpdateCharacterCommand::execute();

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
                $oldCharacter = RandomCharacterGenerator::execute(
                    $command->id(),
                    $command->name(),
                    $command->birthDate(),
                    $command->kingdom(),
                    $command->equipmentId(),
                    $command->factionId(),
                )
            );

        $this->characterRepository
            ->expects(self::once())
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($character),
            );

        ($this->handler)($command);

        self::assertEquals($character::modelName(), Character::modelName());

        self::assertEquals($character->id(), $oldCharacter->id());
        self::assertEquals($character->name(), $oldCharacter->name());
        self::assertEquals($character->birthDate()->toDate(), $oldCharacter->birthDate()->toDate());
        self::assertEquals($character->kingdom(), $oldCharacter->kingdom());
        self::assertEquals($character->equipmentId(), $oldCharacter->equipmentId());
        self::assertEquals($character->factionId(), $oldCharacter->factionId());

        self::assertCount(0, $character->events());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->equipmentRepository = $this->createMock(EquipmentRepository::class);
        $this->factionRepository = $this->createMock(FactionRepository::class);
        $this->characterRepository = $this->createMock(CharacterRepository::class);
        $this->domainEventPublisher = $this->createMock(DomainEventPublisher::class);

        $this->handler = new UpdateCharacterCommandHandler(
            $this->equipmentRepository,
            $this->factionRepository,
            $this->characterRepository,
            $this->domainEventPublisher,
        );
    }
}
