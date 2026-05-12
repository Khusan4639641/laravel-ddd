<?php

namespace App\Domain\Product\Repositories;

use App\Domain\Product\Entities\Product;
use App\Domain\Product\ValueObjects\ProductStatus;

interface ProductRepositoryInterface
{
    /**
     * @return list<Product>
     */
    public function all(bool $includeInactive = false): array;

    public function findById(int $id, bool $includeInactive = false): ?Product;

    public function create(
        string $name,
        ?string $description,
        int $price,
        int $stock,
        ProductStatus $status,
    ): Product;

    /**
     * @param  array{name?: string, description?: string|null, price?: int, stock?: int, status?: ProductStatus}  $data
     */
    public function update(int $id, array $data): ?Product;

    public function delete(int $id): bool;
}
