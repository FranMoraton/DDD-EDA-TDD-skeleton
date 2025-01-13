<?php

declare(strict_types=1);

namespace App\Users\Domain\Model\User;

use App\System\Domain\Model\Aggregate;
use App\Users\Domain\Model\User\Event\UserWasCreated;
use App\Users\Domain\Model\User\Event\UserWasRemoved;
use App\Users\Domain\Model\User\Event\UserWasUpdated;
use App\Users\Domain\Model\User\ValueObject\Email;
use App\Users\Domain\Model\User\ValueObject\Id;
use App\Users\Domain\Model\User\ValueObject\Password;
use App\Users\Domain\Model\User\ValueObject\Role;

class User extends Aggregate
{
    private const string NAME = 'user';

    private function __construct(
        private readonly Id $id,
        private readonly Email $email,
        private readonly Password $password,
        private readonly Role $role
    ) {
        parent::__construct();
    }

    public static function from(
        string $id,
        string $email,
        string $password,
        string $role,
    ): self {
        return new self(
            Id::from($id),
            Email::from($email),
            Password::from($password),
            Role::from($role)
        );
    }

    public static function create(
        string $id,
        string $email,
        #[\SensitiveParameter] string $password,
        string $role,
    ): self {
        $user = new self(
            $idVo = Id::from($id),
            $emailVo = Email::from($email),
            $passwordVo = Password::from($password),
            $roleVo = Role::from($role)
        );

        $user->recordThat(
            UserWasCreated::from($idVo, $emailVo, $roleVo)
        );

        return $user;
    }

    public function update(
        string $email,
        string $role,
    ): self {
        $emailVo = Email::from($email);
        $roleVo = Role::from($role);

        if (
            $this->role === $roleVo
            && $this->email->equalTo($emailVo)
        ) {
            return $this;
        }

        $user = new self(
            $this->id,
            $emailVo,
            $this->password,
            $roleVo,
        );

        $user->recordThat(
            UserWasUpdated::from($this->id, $emailVo, $roleVo)
        );

        return $user;
    }

    public function remove(): self
    {
        $this->recordThat(
            UserWasRemoved::from(
                $this->id,
                $this->email,
                $this->role,
            )
        );

        return $this;
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function password(): Password
    {
        return $this->password;
    }

    public function role(): Role
    {
        return $this->role;
    }

    public static function modelName(): string
    {
        return self::NAME;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'role' => $this->role,
        ];
    }
}
