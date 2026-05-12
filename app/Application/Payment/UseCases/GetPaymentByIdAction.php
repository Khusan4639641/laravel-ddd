<?php

namespace App\Application\Payment\UseCases;

use App\Domain\Payment\Entities\Payment;
use App\Domain\Payment\Exceptions\PaymentNotFoundException;
use App\Domain\Payment\Repositories\PaymentRepositoryInterface;

final readonly class GetPaymentByIdAction
{
    public function __construct(private PaymentRepositoryInterface $payments) {}

    public function execute(int $id, int $userId): Payment
    {
        $payment = $this->payments->findForUser($id, $userId);

        if ($payment === null) {
            throw new PaymentNotFoundException($id);
        }

        return $payment;
    }
}
