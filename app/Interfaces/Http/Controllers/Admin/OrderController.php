<?php

namespace App\Interfaces\Http\Controllers\Admin;

use App\Application\Order\UseCases\CompleteOrderAction;
use App\Application\Order\UseCases\GetAdminOrdersAction;
use App\Application\Order\UseCases\GetOrderByIdAction;
use App\Http\Controllers\Controller;
use App\Interfaces\Http\Resources\OrderResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class OrderController extends Controller
{
    public function index(GetAdminOrdersAction $action): AnonymousResourceCollection
    {
        return OrderResource::collection($action->execute());
    }

    public function show(int $id, GetOrderByIdAction $action): OrderResource
    {
        return OrderResource::make($action->execute($id));
    }

    public function complete(int $id, CompleteOrderAction $action): OrderResource
    {
        return OrderResource::make($action->execute($id));
    }
}
