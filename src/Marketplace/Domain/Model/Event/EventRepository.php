<?php

namespace App\Marketplace\Domain\Model\Event;

use App\Marketplace\Domain\Model\Event\ValueObject\Id;
use App\System\Domain\Criteria\Criteria;

interface EventRepository
{
    public function byId(Id $id): ?Event;
    public function add(Event $event): void;
    public function update(Event $event): void;
    public function remove(Event $event): void;
    /**
     * @param Criteria $criteria
     * @return array<Event>
    */
    public function search(Criteria $criteria): array;
    public function count(Criteria $criteria): int;
}
