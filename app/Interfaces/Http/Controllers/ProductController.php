<?php

namespace App\Interfaces\Http\Controllers;

use App\Application\Product\UseCases\GetProductByIdAction;
use App\Application\Product\UseCases\GetProductListAction;
use App\Http\Controllers\Controller;
use App\Interfaces\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ProductController extends Controller
{
    public function index(GetProductListAction $action): AnonymousResourceCollection
    {
        return ProductResource::collection($action->execute());
    }

    public function show(int $id, GetProductByIdAction $action): ProductResource
    {
        return ProductResource::make($action->execute($id));
    }
}
