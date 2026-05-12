<?php

namespace App\Domain\User\Entities;

use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserRole;

final readonly class User
{
    public function __construct(
        private int $id,
        private string $name,
        private Email $email,
        private UserRole $role,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function role(): UserRole
    {
        return $this->role;
    }
}
