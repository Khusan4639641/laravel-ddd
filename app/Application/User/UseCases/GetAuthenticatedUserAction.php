<?php

namespace App\Application\User\UseCases;

use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Auth\AuthenticationException;

final readonly class GetAuthenticatedUserAction
{
    public function __construct(private UserRepositoryInterface $users) {}

    /**
     * @throws AuthenticationException
     */
    public function execute(int $userId): User
    {
        $user = $this->users->findById($userId);

        if ($user === null) {
            throw new AuthenticationException;
        }

        return $user;
    }
}
