<?php

namespace App\Infrastructure\Queue;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

final class SendOrderPaidEmailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $orderId,
        public int $userId,
        public int $amount,
    ) {}

    public function handle(): void
    {
        Log::info('Order paid notification queued.', [
            'order_id' => $this->orderId,
            'user_id' => $this->userId,
            'amount' => $this->amount,
        ]);
    }
}
