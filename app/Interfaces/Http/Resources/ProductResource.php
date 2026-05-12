<?php

namespace App\Interfaces\Http\Resources;

use App\Domain\Product\Entities\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductResource extends JsonResource
{
    /**
     * @return array<string, int|string|null>
     */
    public function toArray(Request $request): array
    {
        /** @var Product $product */
        $product = $this->resource;

        return [
            'id' => $product->id(),
            'name' => $product->name(),
            'description' => $product->description(),
            'price' => $product->price(),
            'stock' => $product->stock(),
            'status' => $product->status()->value(),
        ];
    }
}
