<?php

declare(strict_types=1);

namespace App\Marketplace\Domain\Model\Event\ValueObject;

use App\System\Domain\ValueObject\CollectionValueObject;

/**
 * @extends CollectionValueObject<int, Zone>
 */
final class Zones extends CollectionValueObject
{
    public static function fromArray(array $zones): self
    {
        $transformedZones = [];

        foreach ($zones as $zone) {
            $transformedZones[] = Zone::fromArray($zone);
        }

        return self::from($transformedZones);
    }
}
