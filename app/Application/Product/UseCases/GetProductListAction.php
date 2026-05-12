<?php

namespace App\Application\Product\UseCases;

use App\Domain\Product\Entities\Product;
use App\Domain\Product\Repositories\ProductRepositoryInterface;

final readonly class GetProductListAction
{
    public function __construct(private ProductRepositoryInterface $products) {}

    /**
     * @return list<Product>
     */
    public function execute(bool $includeInactive = false): array
    {
        return $this->products->all($includeInactive);
    }
}
