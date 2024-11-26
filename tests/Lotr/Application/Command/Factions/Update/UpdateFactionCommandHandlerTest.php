<?php

namespace App\Tests\Lotr\Application\Command\Factions\Update;

use App\Lotr\Application\Command\Factions\Update\UpdateFactionCommandHandler;
use App\Lotr\Domain\Model\Faction\Event\FactionWasUpdated;
use App\Lotr\Domain\Model\Faction\Faction;
use App\Lotr\Domain\Model\Faction\FactionRepository;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\NotFoundException;
use App\Tests\Lotr\Domain\Model\Faction\RandomFactionGenerator;
use App\Tests\System\Infrastructure\PhpUnit\SpyTestHelper;
use PHPUnit\Framework\TestCase;

class UpdateFactionCommandHandlerTest extends TestCase
{
    use SpyTestHelper;

    private FactionRepository $factionRepository;
    private DomainEventPublisher $domainEventPublisher;

    public function testGivenUpdateWhenFactionDoesNotExistThenFail(): void
    {
        $command = RandomUpdateFactionCommand::execute();

        $this->factionRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(null);

        $this->factionRepository
            ->expects(self::never())
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::never())
            ->method('execute');

        $handler = new UpdateFactionCommandHandler(
            $this->factionRepository,
            $this->domainEventPublisher,
        );

        self::expectException(NotFoundException::class);
        $handler($command);
    }

    public function testGivenUpdateCommandWhenFactionAlreadyUpdatedThenDoNotUpdate(): void
    {
        $command = RandomUpdateFactionCommand::execute();

        $this->factionRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(
                $oldFaction = RandomFactionGenerator::execute(
                    $command->id(),
                    $command->name(),
                    $command->description(),
                ),
            );

        $this->factionRepository
            ->expects(self::once())
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($faction),
            );

        $handler = new UpdateFactionCommandHandler(
            $this->factionRepository,
            $this->domainEventPublisher,
        );

        $handler($command);

        self::assertEquals($faction::modelName(), Faction::modelName());
        self::assertEquals($faction->id(), $command->id());
        self::assertEquals($faction->name(), $command->name());
        self::assertEquals($faction->description(), $command->description());

        self::assertCount(0, $faction->events());
    }

    public function testGivenUpdateCommandWhenFactionDescriptionIsDifferentThenUpdate(): void
    {
        $command = RandomUpdateFactionCommand::execute();

        $this->factionRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(
                $oldFaction = RandomFactionGenerator::execute(
                    $command->id(),
                    $command->name(),
                ),
            );

        $this->factionRepository
            ->expects(self::once())
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($faction),
            );

        $handler = new UpdateFactionCommandHandler(
            $this->factionRepository,
            $this->domainEventPublisher,
        );

        $handler($command);

        self::assertEquals($faction::modelName(), Faction::modelName());
        self::assertEquals($faction->id(), $command->id());
        self::assertEquals($faction->name(), $command->name());
        self::assertEquals($faction->description(), $command->description());
        self::assertNotEquals($faction->description(), $oldFaction->description());

        $firstEvent = $faction->events()[array_key_first($faction->events())];
        self::assertEquals($firstEvent::messageName(), FactionWasUpdated::messageName());
    }

    public function testGivenUpdateCommandWhenFactionNameIsDifferentThenUpdate(): void
    {
        $command = RandomUpdateFactionCommand::execute();

        $this->factionRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(
                $oldFaction = RandomFactionGenerator::execute(
                    $command->id(),
                    $command->description(),
                ),
            );

        $this->factionRepository
            ->expects(self::once())
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($faction),
            );

        $handler = new UpdateFactionCommandHandler(
            $this->factionRepository,
            $this->domainEventPublisher,
        );

        $handler($command);

        self::assertEquals($faction::modelName(), Faction::modelName());
        self::assertEquals($faction->id(), $command->id());
        self::assertEquals($faction->name(), $command->name());
        self::assertEquals($faction->description(), $command->description());
        self::assertNotEquals($faction->name(), $oldFaction->name());

        $firstEvent = $faction->events()[array_key_first($faction->events())];
        self::assertEquals($firstEvent::messageName(), FactionWasUpdated::messageName());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->factionRepository = $this->createMock(FactionRepository::class);
        $this->domainEventPublisher = $this->createMock(DomainEventPublisher::class);
    }
}
