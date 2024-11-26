<?php

declare(strict_types=1);

namespace App\Lotr\Application\Query\Equipments\ById;

use App\System\Application\Query;

final class GetByIdQuery implements Query
{
    private const string NAME = 'company.lotr.1.query.equipment.by_id';

    public function __construct(private string $id)
    {
    }

    public function id(): string
    {
        return $this->id;
    }

    public static function messageName(): string
    {
        return self::NAME;
    }
}
