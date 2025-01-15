<?php

declare(strict_types=1);

namespace App\Users\Domain\Model\User\Criteria;

use App\System\Domain\Criteria\Operator;
use App\Users\Domain\Model\User\ValueObject\Email;

final class ByEmailCriteria
{
    public static function create(Email $email): UsersCriteria
    {
        $criteria = new UsersCriteria();
        $criteria->withFilter('email', $email->value(), Operator::EQUALS);
        $criteria->withLimit(1);

        return $criteria;
    }
}
