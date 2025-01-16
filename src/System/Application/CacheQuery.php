<?php

declare(strict_types=1);

namespace App\System\Application;

abstract class CacheQuery extends Query
{
    abstract public function expirationTime(): int;
}
