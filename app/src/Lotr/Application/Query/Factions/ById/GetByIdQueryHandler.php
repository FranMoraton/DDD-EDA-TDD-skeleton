<?php

declare(strict_types=1);

namespace App\Lotr\Application\Query\Factions\ById;

use Doctrine\DBAL\Connection;

final readonly class GetByIdQueryHandler
{
    public function __construct(private Connection $connection)
    {
        // TODO: Implement __call() method.
    }

    public function __invoke(GetByIdQuery $query): array
    {
        $x = $this->connection->fetchAllAssociative('select * from factions');

        var_dump($x);
        die;
    }
}
