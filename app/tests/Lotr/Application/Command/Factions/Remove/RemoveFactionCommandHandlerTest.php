<?php

namespace App\Tests\Lotr\Application\Command\Factions\Remove;

use App\Lotr\Application\Command\Factions\Remove\RemoveFactionCommandHandler;
use App\Lotr\Domain\Model\Faction\Event\FactionWasCreated;
use App\Lotr\Domain\Model\Faction\Faction;
use App\Lotr\Domain\Model\Faction\FactionRepository;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\NotFoundException;
use App\Tests\Lotr\Domain\Model\Faction\RandomFactionGenerator;
use App\Tests\System\Infrastructure\PhpUnit\SpyTestHelper;
use PHPUnit\Framework\TestCase;

class RemoveFactionCommandHandlerTest extends TestCase
{
    use SpyTestHelper;

    private FactionRepository $factionRepository;
    private DomainEventPublisher $domainEventPublisher;

    public function testGivenRemoveWhenFactionDoesNotExistThenSuccess(): void
    {
        $command = RandomRemoveFactionCommand::execute();

        $this->factionRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(
                RandomFactionGenerator::execute($command->id()),
            );

        $this->factionRepository
            ->expects(self::once())
            ->method('remove');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($faction),
            );

        $handler = new RemoveFactionCommandHandler(
            $this->factionRepository,
            $this->domainEventPublisher,
        );

        $handler($command);

        self::assertEquals($faction::modelName(), Faction::modelName());
        self::assertEquals($faction->id(), $command->id());
        $firstEvent = $faction->events()[array_key_first($faction->events())];
        self::assertEquals($firstEvent::messageName(), FactionWasCreated::messageName());
    }

    public function testGivenRemoveCommandWhenFactionDoesNotExistThenFail(): void
    {
        $command = RandomRemoveFactionCommand::execute();

        $this->factionRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(null);

        $this->factionRepository
            ->expects(self::never())
            ->method('remove');

        $this->domainEventPublisher
            ->expects(self::never())
            ->method('execute');

        $handler = new RemoveFactionCommandHandler(
            $this->factionRepository,
            $this->domainEventPublisher,
        );

        self::expectException(NotFoundException::class);
        $handler($command);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->factionRepository = $this->createMock(FactionRepository::class);
        $this->domainEventPublisher = $this->createMock(DomainEventPublisher::class);
    }
}
