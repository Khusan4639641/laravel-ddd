<?php

namespace App\Domain\Payment\Repositories;

use App\Domain\Payment\Entities\Payment;
use App\Domain\Payment\ValueObjects\PaymentProvider;

interface PaymentRepositoryInterface
{
    public function payOrder(int $orderId, int $userId, int $amount, PaymentProvider $provider): Payment;

    /**
     * @return list<Payment>
     */
    public function allForUser(int $userId): array;

    public function findForUser(int $id, int $userId): ?Payment;
}
