<?php

namespace App\Infrastructure\Listeners;

use App\Domain\Payment\Events\PaymentFailed;
use App\Infrastructure\Queue\WriteOrderLogJob;

final class WritePaymentLog
{
    public function handle(PaymentFailed $event): void
    {
        WriteOrderLogJob::dispatch(
            type: 'payment.failed',
            context: [
                'payment_id' => $event->paymentId,
                'order_id' => $event->orderId,
                'user_id' => $event->userId,
                'amount' => $event->amount,
            ],
        );
    }
}
