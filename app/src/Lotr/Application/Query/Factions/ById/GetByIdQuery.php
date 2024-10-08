<?php

declare(strict_types=1);

namespace App\Lotr\Application\Query\Factions\ById;

use App\System\Application\Query;

final class GetByIdQuery implements Query
{
    public static function messageName(): string
    {
        return 'company.lotr.1.query.faction.by_id';
    }
}
