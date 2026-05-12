<?php

namespace App\Interfaces\Http\Resources;

use App\Domain\Order\Entities\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class OrderItemResource extends JsonResource
{
    /**
     * @return array<string, int|string|null>
     */
    public function toArray(Request $request): array
    {
        /** @var OrderItem $item */
        $item = $this->resource;

        return [
            'id' => $item->id(),
            'order_id' => $item->orderId(),
            'product_id' => $item->productId(),
            'quantity' => $item->quantity(),
            'price' => $item->price(),
            'subtotal' => $item->subtotal(),
            'created_at' => $item->createdAt(),
            'updated_at' => $item->updatedAt(),
        ];
    }
}
