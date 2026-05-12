<?php

namespace App\Application\User\UseCases;

use App\Application\User\DTO\LoginUserDTO;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use Illuminate\Validation\ValidationException;

final readonly class LoginUserAction
{
    public function __construct(private UserRepositoryInterface $users) {}

    /**
     * @return array{user: User, token: string}
     */
    public function execute(LoginUserDTO $dto): array
    {
        $user = $this->users->findByCredentials(new Email($dto->email), $dto->password);

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        return [
            'user' => $user,
            'token' => $this->users->createToken($user, 'auth-token'),
        ];
    }
}
