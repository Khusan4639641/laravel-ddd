<?php

namespace App\Application\Order\UseCases;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Domain\Order\Repositories\OrderRepositoryInterface;

final readonly class CompleteOrderAction
{
    public function __construct(private OrderRepositoryInterface $orders) {}

    public function execute(int $id): Order
    {
        $order = $this->orders->complete($id);

        if ($order === null) {
            throw new OrderNotFoundException($id);
        }

        return $order;
    }
}
