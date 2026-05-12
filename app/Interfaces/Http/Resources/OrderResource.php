<?php

namespace App\Interfaces\Http\Resources;

use App\Domain\Order\Entities\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class OrderResource extends JsonResource
{
    /**
     * @return array<string, int|string|null|object>
     */
    public function toArray(Request $request): array
    {
        /** @var Order $order */
        $order = $this->resource;

        return [
            'id' => $order->id(),
            'user_id' => $order->userId(),
            'status' => $order->status()->value(),
            'total_amount' => $order->totalAmount(),
            'items' => OrderItemResource::collection($order->items()),
            'created_at' => $order->createdAt(),
            'updated_at' => $order->updatedAt(),
        ];
    }
}
