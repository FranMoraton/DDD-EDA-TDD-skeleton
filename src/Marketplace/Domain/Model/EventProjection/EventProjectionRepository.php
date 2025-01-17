<?php

namespace App\Marketplace\Domain\Model\EventProjection;

use App\System\Domain\Criteria\Criteria;

interface EventProjectionRepository
{
    public function upsertByEventDate(EventProjection $eventProjection): void;
    public function search(Criteria $criteria): array;
    public function count(Criteria $criteria): int;
}
