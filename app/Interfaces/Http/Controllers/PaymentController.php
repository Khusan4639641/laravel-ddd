<?php

namespace App\Interfaces\Http\Controllers;

use App\Application\Payment\DTO\PayOrderDTO;
use App\Application\Payment\UseCases\GetPaymentByIdAction;
use App\Application\Payment\UseCases\GetUserPaymentsAction;
use App\Application\Payment\UseCases\PayOrderAction;
use App\Http\Controllers\Controller;
use App\Interfaces\Http\Requests\PayOrderRequest;
use App\Interfaces\Http\Resources\PaymentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PaymentController extends Controller
{
    public function pay(int $id, PayOrderRequest $request, PayOrderAction $action): JsonResponse
    {
        $user = $request->user();

        return PaymentResource::make(
            $action->execute(
                orderId: $id,
                userId: (int) $user->getAuthIdentifier(),
                userRole: (string) $user->role,
                dto: PayOrderDTO::fromArray($request->validated()),
            )
        )->response()->setStatusCode(201);
    }

    public function index(Request $request, GetUserPaymentsAction $action): AnonymousResourceCollection
    {
        return PaymentResource::collection(
            $action->execute((int) $request->user()->getAuthIdentifier())
        );
    }

    public function show(int $id, Request $request, GetPaymentByIdAction $action): PaymentResource
    {
        return PaymentResource::make(
            $action->execute($id, (int) $request->user()->getAuthIdentifier())
        );
    }
}
