<?php

namespace App\Application\Product\DTO;

use App\Domain\Product\ValueObjects\ProductStatus;
use InvalidArgumentException;

final readonly class UpdateProductDTO
{
    public ?ProductStatus $status;

    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?int $price = null,
        public ?int $stock = null,
        ?string $status = null,
        public bool $hasDescription = false,
    ) {
        if ($this->price !== null && $this->price < 1) {
            throw new InvalidArgumentException('Product price must be greater than zero.');
        }

        if ($this->stock !== null && $this->stock < 0) {
            throw new InvalidArgumentException('Product stock cannot be negative.');
        }

        $this->status = $status === null ? null : new ProductStatus($status);
    }

    /**
     * @param  array{name?: string, description?: string|null, price?: int, stock?: int, status?: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            price: array_key_exists('price', $data) ? (int) $data['price'] : null,
            stock: array_key_exists('stock', $data) ? (int) $data['stock'] : null,
            status: $data['status'] ?? null,
            hasDescription: array_key_exists('description', $data),
        );
    }

    /**
     * @return array{name?: string, description?: string|null, price?: int, stock?: int, status?: ProductStatus}
     */
    public function toRepositoryData(): array
    {
        $data = [];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        if ($this->hasDescription) {
            $data['description'] = $this->description;
        }

        if ($this->price !== null) {
            $data['price'] = $this->price;
        }

        if ($this->stock !== null) {
            $data['stock'] = $this->stock;
        }

        if ($this->status !== null) {
            $data['status'] = $this->status;
        }

        return $data;
    }
}
