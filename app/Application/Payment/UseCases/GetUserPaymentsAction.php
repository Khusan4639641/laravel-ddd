<?php

namespace App\Application\Payment\UseCases;

use App\Domain\Payment\Entities\Payment;
use App\Domain\Payment\Repositories\PaymentRepositoryInterface;

final readonly class GetUserPaymentsAction
{
    public function __construct(private PaymentRepositoryInterface $payments) {}

    /**
     * @return list<Payment>
     */
    public function execute(int $userId): array
    {
        return $this->payments->allForUser($userId);
    }
}
