<?php

declare(strict_types=1);

namespace App\Users\Domain\Model\User\Criteria;

use App\System\Domain\Criteria\Criteria;

final class UsersCriteria extends Criteria
{
    protected function allowedFields(): array
    {
        return [
            'email' => 'string',
            'role' => 'string',
        ];
    }
}
