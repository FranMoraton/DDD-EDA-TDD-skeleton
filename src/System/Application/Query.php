<?php

declare(strict_types=1);

namespace App\System\Application;

abstract class Query extends Message
{
    abstract public function rebuildPayload(): void;
    abstract public static function messageName(): string;
}
