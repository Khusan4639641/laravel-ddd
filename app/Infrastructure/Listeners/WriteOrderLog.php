<?php

namespace App\Infrastructure\Listeners;

use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Order\Events\OrderCreated;
use App\Domain\Order\Events\OrderPaid;
use App\Infrastructure\Queue\WriteOrderLogJob;

final class WriteOrderLog
{
    public function handle(OrderCreated|OrderCancelled|OrderPaid $event): void
    {
        WriteOrderLogJob::dispatch(
            type: $this->type($event),
            context: $this->context($event),
        );
    }

    private function type(OrderCreated|OrderCancelled|OrderPaid $event): string
    {
        return match ($event::class) {
            OrderCreated::class => 'order.created',
            OrderCancelled::class => 'order.cancelled',
            OrderPaid::class => 'order.paid',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function context(OrderCreated|OrderCancelled|OrderPaid $event): array
    {
        return match ($event::class) {
            OrderCreated::class, OrderCancelled::class => [
                'order_id' => $event->orderId,
                'user_id' => $event->userId,
                'total_amount' => $event->totalAmount,
                'items' => $event->items,
            ],
            OrderPaid::class => [
                'order_id' => $event->orderId,
                'payment_id' => $event->paymentId,
                'user_id' => $event->userId,
                'amount' => $event->amount,
            ],
        };
    }
}
