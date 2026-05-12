<?php

namespace App\Domain\Product\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final readonly class ProductStockReduced
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $productId,
        public int $orderId,
        public int $quantity,
        public int $stock,
    ) {}
}
