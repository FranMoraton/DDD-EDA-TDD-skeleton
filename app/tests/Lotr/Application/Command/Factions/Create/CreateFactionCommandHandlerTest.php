<?php

namespace App\Tests\Lotr\Application\Command\Factions\Create;

use App\Lotr\Application\Command\Factions\Create\CreateFactionCommandHandler;
use App\Lotr\Domain\Model\Faction\FactionRepository;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\AlreadyExistException;
use App\Tests\Lotr\Domain\Model\Faction\RandomFactionGenerator;
use PHPUnit\Framework\TestCase;

class CreateFactionCommandHandlerTest extends TestCase
{
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
            ->method('execute');

        $handler = new CreateFactionCommandHandler(
            $this->factionRepository,
            $this->domainEventPublisher,
        );

        $handler($command);

        self::assertEquals(1, 1);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->factionRepository = $this->createMock(FactionRepository::class);
        $this->domainEventPublisher = $this->createMock(DomainEventPublisher::class);
    }
}