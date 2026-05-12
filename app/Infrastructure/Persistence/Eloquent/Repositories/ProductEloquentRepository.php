<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Product\Entities\Product;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\ValueObjects\ProductStatus;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Illuminate\Database\Eloquent\Builder;

final class ProductEloquentRepository implements ProductRepositoryInterface
{
    public function all(bool $includeInactive = false): array
    {
        return $this->query($includeInactive)
            ->latest('id')
            ->get()
            ->map(fn (ProductModel $product): Product => $this->toDomain($product))
            ->all();
    }

    public function findById(int $id, bool $includeInactive = false): ?Product
    {
        $product = $this->query($includeInactive)->find($id);

        return $product === null ? null : $this->toDomain($product);
    }

    public function create(
        string $name,
        ?string $description,
        int $price,
        int $stock,
        ProductStatus $status,
    ): Product {
        $product = ProductModel::query()->create([
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'status' => $status->value(),
        ]);

        return $this->toDomain($product);
    }

    public function update(int $id, array $data): ?Product
    {
        $product = ProductModel::query()->find($id);

        if ($product === null) {
            return null;
        }

        $payload = [];

        foreach ($data as $key => $value) {
            $payload[$key] = $value instanceof ProductStatus ? $value->value() : $value;
        }

        $product->fill($payload);
        $product->save();

        return $this->toDomain($product->refresh());
    }

    public function delete(int $id): bool
    {
        $product = ProductModel::query()->find($id);

        if ($product === null) {
            return false;
        }

        return (bool) $product->delete();
    }

    /**
     * @return Builder<ProductModel>
     */
    private function query(bool $includeInactive): Builder
    {
        return ProductModel::query()
            ->when(! $includeInactive, function (Builder $query): void {
                $query->where('status', ProductStatus::ACTIVE);
            });
    }

    private function toDomain(ProductModel $product): Product
    {
        return new Product(
            id: (int) $product->id,
            name: $product->name,
            description: $product->description,
            price: (int) $product->price,
            stock: (int) $product->stock,
            status: new ProductStatus($product->status),
        );
    }
}
