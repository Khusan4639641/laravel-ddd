<?php

namespace App\Infrastructure\Listeners;

use App\Domain\Product\Events\ProductStockRestored;
use Illuminate\Support\Facades\Log;

final class RestoreProductStock
{
    public function handle(ProductStockRestored $event): void
    {
        Log::info('Product stock restored.', [
            'product_id' => $event->productId,
            'order_id' => $event->orderId,
            'quantity' => $event->quantity,
            'stock' => $event->stock,
        ]);
    }
}
