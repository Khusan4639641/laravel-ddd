<?php

namespace App\Domain\Payment\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final readonly class PaymentFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $paymentId,
        public int $orderId,
        public int $userId,
        public int $amount,
    ) {}
}
