<?php

declare(strict_types=1);

namespace App\System\Application;

interface Command
{
    public static function messageName(): string;
}
