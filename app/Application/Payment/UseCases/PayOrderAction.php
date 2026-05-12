<?php

namespace App\Application\Payment\UseCases;

use App\Application\Payment\DTO\PayOrderDTO;
use App\Domain\Payment\Entities\Payment;
use App\Domain\Payment\Repositories\PaymentRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;

final readonly class PayOrderAction
{
    public function __construct(private PaymentRepositoryInterface $payments) {}

    /**
     * @throws AuthorizationException
     */
    public function execute(int $orderId, int $userId, string $userRole, PayOrderDTO $dto): Payment
    {
        if ($userRole === 'admin') {
            throw new AuthorizationException('Admins cannot pay user orders.');
        }

        return $this->payments->payOrder(
            orderId: $orderId,
            userId: $userId,
            amount: $dto->amount,
            provider: $dto->provider,
        );
    }
}
