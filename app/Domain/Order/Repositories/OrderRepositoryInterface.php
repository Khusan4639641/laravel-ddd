<?php

namespace App\Domain\Order\Repositories;

use App\Domain\Order\Entities\Order;

interface OrderRepositoryInterface
{
    /**
     * @param  list<array{product_id: int, quantity: int}>  $items
     */
    public function create(int $userId, array $items): Order;

    /**
     * @return list<Order>
     */
    public function allForUser(int $userId): array;

    /**
     * @return list<Order>
     */
    public function all(): array;

    public function findForUser(int $id, int $userId): ?Order;

    public function findById(int $id): ?Order;

    public function cancelForUser(int $id, int $userId): ?Order;

    public function complete(int $id): ?Order;
}
