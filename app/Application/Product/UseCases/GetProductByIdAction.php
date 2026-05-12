<?php

namespace App\Application\Product\UseCases;

use App\Domain\Product\Entities\Product;
use App\Domain\Product\Exceptions\ProductNotFoundException;
use App\Domain\Product\Repositories\ProductRepositoryInterface;

final readonly class GetProductByIdAction
{
    public function __construct(private ProductRepositoryInterface $products) {}

    public function execute(int $id, bool $includeInactive = false): Product
    {
        $product = $this->products->findById($id, $includeInactive);

        if ($product === null) {
            throw new ProductNotFoundException($id);
        }

        return $product;
    }
}
