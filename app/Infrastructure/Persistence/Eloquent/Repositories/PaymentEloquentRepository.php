<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Order\Events\OrderPaid;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Domain\Payment\Entities\Payment;
use App\Domain\Payment\Events\PaymentFailed;
use App\Domain\Payment\Exceptions\InvalidPaymentException;
use App\Domain\Payment\Exceptions\PaymentAlreadyProcessedException;
use App\Domain\Payment\Exceptions\PaymentNotFoundException;
use App\Domain\Payment\Repositories\PaymentRepositoryInterface;
use App\Domain\Payment\ValueObjects\PaymentProvider;
use App\Domain\Payment\ValueObjects\PaymentStatus;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\PaymentModel;
use App\Infrastructure\Services\FakePaymentProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final readonly class PaymentEloquentRepository implements PaymentRepositoryInterface
{
    public function __construct(private FakePaymentProvider $fakePaymentProvider) {}

    public function payOrder(int $orderId, int $userId, int $amount, PaymentProvider $provider): Payment
    {
        $payment = DB::transaction(function () use ($orderId, $userId, $amount, $provider): Payment {
            $order = OrderModel::query()
                ->whereKey($orderId)
                ->lockForUpdate()
                ->first();

            if ($order === null || (int) $order->user_id !== $userId) {
                throw new PaymentNotFoundException($orderId);
            }

            if ($order->status === OrderStatus::PAID) {
                throw new PaymentAlreadyProcessedException('Paid order cannot be paid again.');
            }

            if ($order->status !== OrderStatus::PENDING) {
                throw new InvalidPaymentException('Only pending orders can be paid.');
            }

            if ((int) $order->total_amount !== $amount) {
                throw new InvalidPaymentException('Payment amount must match order total amount.');
            }

            $payment = PaymentModel::query()->create([
                'order_id' => $order->id,
                'user_id' => $userId,
                'amount' => $amount,
                'status' => PaymentStatus::PENDING,
                'provider' => $provider->value(),
                'transaction_id' => null,
            ]);

            $result = $this->fakePaymentProvider->charge($amount);

            $payment->status = $result['successful'] ? PaymentStatus::SUCCESS : PaymentStatus::FAILED;
            $payment->transaction_id = $result['transaction_id'];
            $payment->save();

            if ($result['successful']) {
                $order->status = OrderStatus::PAID;
                $order->save();
            }

            return $this->toDomain($payment->refresh());
        });

        if ($payment->status()->isSuccess()) {
            Event::dispatch(new OrderPaid(
                orderId: $payment->orderId(),
                paymentId: $payment->id(),
                userId: $payment->userId(),
                amount: $payment->amount(),
            ));
        } else {
            Event::dispatch(new PaymentFailed(
                paymentId: $payment->id(),
                orderId: $payment->orderId(),
                userId: $payment->userId(),
                amount: $payment->amount(),
            ));
        }

        return $payment;
    }

    public function allForUser(int $userId): array
    {
        return PaymentModel::query()
            ->where('user_id', $userId)
            ->latest('id')
            ->get()
            ->map(fn (PaymentModel $payment): Payment => $this->toDomain($payment))
            ->all();
    }

    public function findForUser(int $id, int $userId): ?Payment
    {
        $payment = PaymentModel::query()
            ->where('user_id', $userId)
            ->find($id);

        return $payment === null ? null : $this->toDomain($payment);
    }

    private function toDomain(PaymentModel $payment): Payment
    {
        return new Payment(
            id: (int) $payment->id,
            orderId: (int) $payment->order_id,
            userId: (int) $payment->user_id,
            amount: (int) $payment->amount,
            status: new PaymentStatus($payment->status),
            provider: new PaymentProvider($payment->provider),
            transactionId: $payment->transaction_id,
            createdAt: $payment->created_at?->toISOString(),
            updatedAt: $payment->updated_at?->toISOString(),
        );
    }
}
