<?php

namespace App\Application\Product\DTO;

use App\Domain\Product\ValueObjects\ProductStatus;
use InvalidArgumentException;

final readonly class CreateProductDTO
{
    public ProductStatus $status;

    public function __construct(
        public string $name,
        public ?string $description,
        public int $price,
        public int $stock,
        string $status,
    ) {
        if ($this->price < 1) {
            throw new InvalidArgumentException('Product price must be greater than zero.');
        }

        if ($this->stock < 0) {
            throw new InvalidArgumentException('Product stock cannot be negative.');
        }

        $this->status = new ProductStatus($status);
    }

    /**
     * @param  array{name: string, description?: string|null, price: int, stock: int, status: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            price: (int) $data['price'],
            stock: (int) $data['stock'],
            status: $data['status'],
        );
    }
}
