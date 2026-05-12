<?php

namespace App\Interfaces\Http\Resources;

use App\Domain\User\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserResource extends JsonResource
{
    /**
     * @return array<string, int|string>
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;

        return [
            'id' => $user->id(),
            'name' => $user->name(),
            'email' => $user->email()->value(),
            'role' => $user->role()->value(),
        ];
    }
}
