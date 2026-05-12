<?php

namespace App\Infrastructure\Listeners;

use App\Domain\Order\Events\OrderPaid;
use App\Infrastructure\Queue\SendOrderPaidEmailJob;

final class SendOrderPaidNotification
{
    public function handle(OrderPaid $event): void
    {
        SendOrderPaidEmailJob::dispatch(
            orderId: $event->orderId,
            userId: $event->userId,
            amount: $event->amount,
        );
    }
}
