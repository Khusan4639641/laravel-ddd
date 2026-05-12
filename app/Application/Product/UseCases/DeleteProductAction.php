<?php

namespace App\Application\Product\UseCases;

use App\Domain\Product\Exceptions\ProductNotFoundException;
use App\Domain\Product\Repositories\ProductRepositoryInterface;

final readonly class DeleteProductAction
{
    public function __construct(private ProductRepositoryInterface $products) {}

    public function execute(int $id): void
    {
        if (! $this->products->delete($id)) {
            throw new ProductNotFoundException($id);
        }
    }
}
