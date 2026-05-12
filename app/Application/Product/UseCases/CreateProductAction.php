<?php

namespace App\Application\Product\UseCases;

use App\Application\Product\DTO\CreateProductDTO;
use App\Domain\Product\Entities\Product;
use App\Domain\Product\Repositories\ProductRepositoryInterface;

final readonly class CreateProductAction
{
    public function __construct(private ProductRepositoryInterface $products) {}

    public function execute(CreateProductDTO $dto): Product
    {
        return $this->products->create(
            name: $dto->name,
            description: $dto->description,
            price: $dto->price,
            stock: $dto->stock,
            status: $dto->status,
        );
    }
}
