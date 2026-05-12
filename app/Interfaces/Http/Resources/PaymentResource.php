<?php

namespace App\Interfaces\Http\Resources;

use App\Domain\Payment\Entities\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PaymentResource extends JsonResource
{
    /**
     * @return array<string, int|string|null>
     */
    public function toArray(Request $request): array
    {
        /** @var Payment $payment */
        $payment = $this->resource;

        return [
            'id' => $payment->id(),
            'order_id' => $payment->orderId(),
            'user_id' => $payment->userId(),
            'amount' => $payment->amount(),
            'status' => $payment->status()->value(),
            'provider' => $payment->provider()->value(),
            'transaction_id' => $payment->transactionId(),
            'created_at' => $payment->createdAt(),
            'updated_at' => $payment->updatedAt(),
        ];
    }
}
