<?php

namespace App\Domain\Product\Entities;

use App\Domain\Product\ValueObjects\ProductStatus;
use InvalidArgumentException;

final readonly class Product
{
    public function __construct(
        private int $id,
        private string $name,
        private ?string $description,
        private int $price,
        private int $stock,
        private ProductStatus $status,
    ) {
        if ($this->price < 1) {
            throw new InvalidArgumentException('Product price must be greater than zero.');
        }

        if ($this->stock < 0) {
            throw new InvalidArgumentException('Product stock cannot be negative.');
        }
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function price(): int
    {
        return $this->price;
    }

    public function stock(): int
    {
        return $this->stock;
    }

    public function status(): ProductStatus
    {
        return $this->status;
    }
}
