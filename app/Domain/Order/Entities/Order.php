<?php

namespace App\Domain\Order\Entities;

use App\Domain\Order\ValueObjects\OrderStatus;

final readonly class Order
{
    /**
     * @param  list<OrderItem>  $items
     */
    public function __construct(
        private int $id,
        private int $userId,
        private OrderStatus $status,
        private int $totalAmount,
        private array $items = [],
        private ?string $createdAt = null,
        private ?string $updatedAt = null,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    public function totalAmount(): int
    {
        return $this->totalAmount;
    }

    /**
     * @return list<OrderItem>
     */
    public function items(): array
    {
        return $this->items;
    }

    public function createdAt(): ?string
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?string
    {
        return $this->updatedAt;
    }
}
