<?php

namespace App\Tests\Lotr\Application\Command\Factions\Create;

use App\Lotr\Application\Command\Factions\Create\CreateFactionCommandHandler;
use App\Lotr\Domain\Model\Faction\Event\FactionWasCreated;
use App\Lotr\Domain\Model\Faction\Faction;
use App\Lotr\Domain\Model\Faction\FactionRepository;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\AlreadyExistException;
use App\Tests\Lotr\Domain\Model\Faction\RandomFactionGenerator;
use App\Tests\System\Infrastructure\PhpUnit\SpyTestHelper;
use PHPUnit\Framework\TestCase;

class CreateFactionCommandHandlerTest extends TestCase
{
    use SpyTestHelper;

    private FactionRepository $factionRepository;
    private DomainEventPublisher $domainEventPublisher;

    public function testGivenCreateWhenFactionDoesNotExistThenSuccess(): void
    {
        $command = RandomCreateFactionCommand::execute();

        $this->factionRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(
                RandomFactionGenerator::execute(),
            );

        $this->factionRepository
            ->expects(self::never())
            ->method('add');

        $this->domainEventPublisher
            ->expects(self::never())
            ->method('execute');

        $handler = new CreateFactionCommandHandler(
            $this->factionRepository,
            $this->domainEventPublisher,
        );

        self::expectException(AlreadyExistException::class);
        $handler($command);
    }

    public function testGivenCreateCommandWhenFactionExistThenFail(): void
    {
        $command = RandomCreateFactionCommand::execute();

        $this->factionRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            );

        $this->factionRepository
            ->expects(self::once())
            ->method('add');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($faction),
            );

        $handler = new CreateFactionCommandHandler(
            $this->factionRepository,
            $this->domainEventPublisher,
        );

        $handler($command);

        self::assertEquals($faction::modelName(), Faction::modelName());
        self::assertEquals($faction->id(), $command->id());
        self::assertEquals($faction->name(), $command->name());
        self::assertEquals($faction->description(), $command->description());
        $firstEvent = $faction->events()[array_key_first($faction->events())];
        self::assertEquals($firstEvent::messageName(), FactionWasCreated::messageName());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->factionRepository = $this->createMock(FactionRepository::class);
        $this->domainEventPublisher = $this->createMock(DomainEventPublisher::class);
    }
}
