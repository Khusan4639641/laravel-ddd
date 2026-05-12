<?php

namespace App\Domain\Order\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final readonly class OrderCancelled
{
    use Dispatchable, SerializesModels;

    /**
     * @param  list<array{product_id: int, quantity: int, price: int, subtotal: int}>  $items
     */
    public function __construct(
        public int $orderId,
        public int $userId,
        public int $totalAmount,
        public array $items,
    ) {}
}
