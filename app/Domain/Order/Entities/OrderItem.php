<?php

namespace App\Domain\Order\Entities;

final readonly class OrderItem
{
    public function __construct(
        private int $id,
        private int $orderId,
        private int $productId,
        private int $quantity,
        private int $price,
        private int $subtotal,
        private ?string $createdAt = null,
        private ?string $updatedAt = null,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function orderId(): int
    {
        return $this->orderId;
    }

    public function productId(): int
    {
        return $this->productId;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function price(): int
    {
        return $this->price;
    }

    public function subtotal(): int
    {
        return $this->subtotal;
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
