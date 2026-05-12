<?php

namespace App\Application\Order\DTO;

use App\Domain\Order\ValueObjects\Quantity;

final readonly class CreateOrderItemDTO
{
    public Quantity $quantity;

    public function __construct(
        public int $productId,
        int $quantity,
    ) {
        $this->quantity = new Quantity($quantity);
    }

    /**
     * @param  array{product_id: int, quantity: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            productId: (int) $data['product_id'],
            quantity: (int) $data['quantity'],
        );
    }

    /**
     * @return array{product_id: int, quantity: int}
     */
    public function toRepositoryData(): array
    {
        return [
            'product_id' => $this->productId,
            'quantity' => $this->quantity->value(),
        ];
    }
}
