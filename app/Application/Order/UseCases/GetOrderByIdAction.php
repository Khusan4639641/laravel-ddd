<?php

namespace App\Application\Order\UseCases;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Domain\Order\Repositories\OrderRepositoryInterface;

final readonly class GetOrderByIdAction
{
    public function __construct(private OrderRepositoryInterface $orders) {}

    public function execute(int $id, ?int $userId = null): Order
    {
        $order = $userId === null
            ? $this->orders->findById($id)
            : $this->orders->findForUser($id, $userId);

        if ($order === null) {
            throw new OrderNotFoundException($id);
        }

        return $order;
    }
}
