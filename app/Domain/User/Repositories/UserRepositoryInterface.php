<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\User;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserRole;

interface UserRepositoryInterface
{
    public function existsByEmail(Email $email): bool;

    public function create(string $name, Email $email, string $passwordHash, UserRole $role): User;

    public function findById(int $id): ?User;

    public function findByCredentials(Email $email, string $plainPassword): ?User;

    public function createToken(User $user, string $tokenName): string;

    public function deleteCurrentToken(int $userId, ?string $plainTextToken): void;
}
