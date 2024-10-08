<?php

namespace App\Lotr\Application\Command\Factions\Create;

use App\System\Application\Command;
use Doctrine\DBAL\Connection;

final readonly class CreateFactionCommandHandler
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(CreateFactionCommand $command): void
    {
        $x = $this->connection->fetchAllAssociative('select * from factions');
    }
}