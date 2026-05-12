<?php

namespace App\Infrastructure\Listeners;

use App\Domain\Product\Events\ProductStockReduced;
use Illuminate\Support\Facades\Log;

final class ReduceProductStock
{
    public function handle(ProductStockReduced $event): void
    {
        Log::info('Product stock reduced.', [
            'product_id' => $event->productId,
            'order_id' => $event->orderId,
            'quantity' => $event->quantity,
            'stock' => $event->stock,
        ]);
    }
}
