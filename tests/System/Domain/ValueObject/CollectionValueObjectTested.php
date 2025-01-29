<?php

declare(strict_types=1);

namespace App\Tests\System\Domain\ValueObject;

use App\System\Domain\ValueObject\CollectionValueObject;

/**
 * @extends CollectionValueObject<int, int>
 */
final class CollectionValueObjectTested extends CollectionValueObject
{
    public function add($item): self
    {
        return $this->addItem($item);
    }

    public function remove($item): self
    {
        return $this->removeItem($item);
    }
}
