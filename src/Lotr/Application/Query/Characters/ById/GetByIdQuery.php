<?php

declare(strict_types=1);

namespace App\Lotr\Application\Query\Characters\ById;

use App\System\Application\Query;

final class GetByIdQuery implements Query
{
    private const string NAME = 'company.lotr.1.query.character.by_id';

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
