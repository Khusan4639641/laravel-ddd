<?php

namespace App\Interfaces\Http\Controllers;

use App\Application\Order\DTO\CreateOrderDTO;
use App\Application\Order\UseCases\CancelOrderAction;
use App\Application\Order\UseCases\CreateOrderAction;
use App\Application\Order\UseCases\GetOrderByIdAction;
use App\Application\Order\UseCases\GetUserOrdersAction;
use App\Http\Controllers\Controller;
use App\Interfaces\Http\Requests\CreateOrderRequest;
use App\Interfaces\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class OrderController extends Controller
{
    public function store(CreateOrderRequest $request, CreateOrderAction $action): JsonResponse
    {
        return OrderResource::make(
            $action->execute(
                userId: (int) $request->user()->getAuthIdentifier(),
                dto: CreateOrderDTO::fromArray($request->validated()),
            )
        )->response()->setStatusCode(201);
    }

    public function index(Request $request, GetUserOrdersAction $action): AnonymousResourceCollection
    {
        return OrderResource::collection(
            $action->execute((int) $request->user()->getAuthIdentifier())
        );
    }

    public function show(int $id, Request $request, GetOrderByIdAction $action): OrderResource
    {
        return OrderResource::make(
            $action->execute($id, (int) $request->user()->getAuthIdentifier())
        );
    }

    public function cancel(int $id, Request $request, CancelOrderAction $action): OrderResource
    {
        return OrderResource::make(
            $action->execute($id, (int) $request->user()->getAuthIdentifier())
        );
    }
}
