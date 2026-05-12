<?php

namespace App\Application\Order\UseCases;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Repositories\OrderRepositoryInterface;

final readonly class GetUserOrdersAction
{
    public function __construct(private OrderRepositoryInterface $orders) {}

    /**
     * @return list<Order>
     */
    public function execute(int $userId): array
    {
        return $this->orders->allForUser($userId);
    }
}
