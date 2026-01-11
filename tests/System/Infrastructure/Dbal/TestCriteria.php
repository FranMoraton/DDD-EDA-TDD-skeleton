<?php

declare(strict_types=1);

namespace App\Tests\System\Infrastructure\Dbal;

use App\System\Domain\Criteria\Criteria;

final class TestCriteria extends Criteria
{
    protected function allowedFields(): array
    {
        return [
            'status' => 'string',
            'url' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'config.timeout' => 'int',
            'config.model' => 'string',
            'config.enabled' => 'bool',
            'config.rate' => 'float',
            'slots[].activationDate' => 'datetime',
            'slots[].deactivationDate' => 'datetime',
            'slots[].status' => 'string',
            'slots[].code' => 'string',
            'slots[].label' => 'string',
        ];
    }
}
