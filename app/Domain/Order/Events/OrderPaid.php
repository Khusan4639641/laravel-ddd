<?php

namespace App\Domain\Order\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final readonly class OrderPaid
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $orderId,
        public int $paymentId,
        public int $userId,
        public int $amount,
    ) {}
}
