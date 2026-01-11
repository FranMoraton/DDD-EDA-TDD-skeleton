<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class SecurityUser implements UserInterface, PasswordAuthenticatedUserInterface, JWTUserInterface
{
    private string $id;
    private string $email;
    private string $password;
    private array $roles;

    private function __construct(
        string $id,
        string $email,
        string $password,
        array $roles,
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->roles = $roles;
    }

    public static function from(
        string $id,
        string $email,
        string $password,
        array $roles,
    ): static {
        return new static($id, $email, $password, $roles);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function roles(): array
    {
        return $this->roles;
    }

    public function getUserIdentifier(): string
    {
        \assert('' !== $this->email);

        return $this->email;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void
    {
        $this->password = '';
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public static function createFromPayload($username, array $payload): JWTUserInterface
    {
        return self::from(
            $payload['id'],
            $payload['email'],
            '',
            $payload['roles'],
        );
    }
}
