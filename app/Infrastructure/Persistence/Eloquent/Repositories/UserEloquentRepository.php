<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserRole;
use App\Models\User as EloquentUser;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

final class UserEloquentRepository implements UserRepositoryInterface
{
    public function existsByEmail(Email $email): bool
    {
        return EloquentUser::query()
            ->where('email', $email->value())
            ->exists();
    }

    public function create(string $name, Email $email, string $passwordHash, UserRole $role): User
    {
        $user = EloquentUser::query()->create([
            'name' => $name,
            'email' => $email->value(),
            'password' => $passwordHash,
            'role' => $role->value(),
        ]);

        return $this->toDomain($user);
    }

    public function findById(int $id): ?User
    {
        $user = EloquentUser::query()->find($id);

        return $user === null ? null : $this->toDomain($user);
    }

    public function findByCredentials(Email $email, string $plainPassword): ?User
    {
        $user = EloquentUser::query()
            ->where('email', $email->value())
            ->first();

        if ($user === null || ! Hash::check($plainPassword, $user->password)) {
            return null;
        }

        return $this->toDomain($user);
    }

    public function createToken(User $user, string $tokenName): string
    {
        $eloquentUser = EloquentUser::query()->findOrFail($user->id());

        return $eloquentUser->createToken($tokenName)->plainTextToken;
    }

    public function deleteCurrentToken(int $userId, ?string $plainTextToken): void
    {
        if ($plainTextToken === null || $plainTextToken === '') {
            EloquentUser::query()->find($userId)?->tokens()->delete();

            return;
        }

        $accessTokenModel = Sanctum::personalAccessTokenModel();
        $accessToken = $accessTokenModel::findToken($plainTextToken);

        if (
            $accessToken !== null
            && (int) $accessToken->tokenable_id === $userId
            && $accessToken->tokenable_type === EloquentUser::class
        ) {
            $accessToken->delete();
        }
    }

    private function toDomain(EloquentUser $user): User
    {
        return new User(
            id: (int) $user->id,
            name: $user->name,
            email: new Email($user->email),
            role: new UserRole($user->role ?? UserRole::USER),
        );
    }
}
