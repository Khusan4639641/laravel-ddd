<?php

namespace App\Application\User\UseCases;

use App\Domain\User\Repositories\UserRepositoryInterface;

final readonly class LogoutUserAction
{
    public function __construct(private UserRepositoryInterface $users) {}

    public function execute(int $userId, ?string $plainTextToken): void
    {
        $this->users->deleteCurrentToken($userId, $plainTextToken);
    }
}
