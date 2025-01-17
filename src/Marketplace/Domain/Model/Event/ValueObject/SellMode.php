<?php

declare(strict_types=1);

namespace App\Marketplace\Domain\Model\Event\ValueObject;

use App\System\Domain\ValueObject\StringValueObject;

final class SellMode extends StringValueObject
{
    public const string ONLINE = 'online';
    public const string OFFLINE = 'offline';
}
