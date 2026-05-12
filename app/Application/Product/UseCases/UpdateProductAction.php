<?php

namespace App\Application\Product\UseCases;

use App\Application\Product\DTO\UpdateProductDTO;
use App\Domain\Product\Entities\Product;
use App\Domain\Product\Exceptions\ProductNotFoundException;
use App\Domain\Product\Repositories\ProductRepositoryInterface;

final readonly class UpdateProductAction
{
    public function __construct(private ProductRepositoryInterface $products) {}

    public function execute(int $id, UpdateProductDTO $dto): Product
    {
        $product = $this->products->update($id, $dto->toRepositoryData());

        if ($product === null) {
            throw new ProductNotFoundException($id);
        }

        return $product;
    }
}
