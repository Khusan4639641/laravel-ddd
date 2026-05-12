<?php

namespace App\Application\User\UseCases;

use App\Application\User\DTO\RegisterUserDTO;
use App\Domain\User\Entities\User;
use App\Domain\User\Exceptions\UserAlreadyExistsException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserRole;
use Illuminate\Support\Facades\Hash;

final readonly class RegisterUserAction
{
    public function __construct(private UserRepositoryInterface $users) {}

    /**
     * @return array{user: User, token: string}
     */
    public function execute(RegisterUserDTO $dto): array
    {
        $email = new Email($dto->email);

        if ($this->users->existsByEmail($email)) {
            throw new UserAlreadyExistsException($email);
        }

        $user = $this->users->create(
            name: $dto->name,
            email: $email,
            passwordHash: Hash::make($dto->password),
            role: UserRole::user(),
        );

        return [
            'user' => $user,
            'token' => $this->users->createToken($user, 'auth-token'),
        ];
    }
}
