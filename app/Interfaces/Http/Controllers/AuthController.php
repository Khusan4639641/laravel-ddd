<?php

namespace App\Interfaces\Http\Controllers;

use App\Application\User\DTO\LoginUserDTO;
use App\Application\User\DTO\RegisterUserDTO;
use App\Application\User\UseCases\GetAuthenticatedUserAction;
use App\Application\User\UseCases\LoginUserAction;
use App\Application\User\UseCases\LogoutUserAction;
use App\Application\User\UseCases\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Interfaces\Http\Requests\LoginRequest;
use App\Interfaces\Http\Requests\RegisterRequest;
use App\Interfaces\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $result = $action->execute(RegisterUserDTO::fromArray($request->validated()));

        return UserResource::make($result['user'])
            ->additional(['token' => $result['token']])
            ->response()
            ->setStatusCode(201);
    }

    public function login(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        $result = $action->execute(LoginUserDTO::fromArray($request->validated()));

        return UserResource::make($result['user'])
            ->additional(['token' => $result['token']])
            ->response();
    }

    public function logout(Request $request, LogoutUserAction $action): JsonResponse
    {
        $action->execute((int) $request->user()->getAuthIdentifier(), $request->bearerToken());

        return response()->json([
            'message' => 'Logged out.',
        ]);
    }

    public function me(Request $request, GetAuthenticatedUserAction $action): UserResource
    {
        return UserResource::make(
            $action->execute((int) $request->user()->getAuthIdentifier())
        );
    }
}
