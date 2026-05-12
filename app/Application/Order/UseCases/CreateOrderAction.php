<?php

namespace App\Application\Order\UseCases;

use App\Application\Order\DTO\CreateOrderDTO;
use App\Domain\Order\Entities\Order;
use App\Domain\Order\Repositories\OrderRepositoryInterface;

final readonly class CreateOrderAction
{
    public function __construct(private OrderRepositoryInterface $orders) {}

    public function execute(int $userId, CreateOrderDTO $dto): Order
    {
        return $this->orders->create($userId, $dto->toRepositoryData());
    }
}
