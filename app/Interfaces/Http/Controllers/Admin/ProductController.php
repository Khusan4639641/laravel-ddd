<?php

namespace App\Interfaces\Http\Controllers\Admin;

use App\Application\Product\DTO\CreateProductDTO;
use App\Application\Product\DTO\UpdateProductDTO;
use App\Application\Product\UseCases\CreateProductAction;
use App\Application\Product\UseCases\DeleteProductAction;
use App\Application\Product\UseCases\GetProductByIdAction;
use App\Application\Product\UseCases\GetProductListAction;
use App\Application\Product\UseCases\UpdateProductAction;
use App\Http\Controllers\Controller;
use App\Interfaces\Http\Requests\CreateProductRequest;
use App\Interfaces\Http\Requests\UpdateProductRequest;
use App\Interfaces\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class ProductController extends Controller
{
    public function index(GetProductListAction $action): AnonymousResourceCollection
    {
        return ProductResource::collection($action->execute(includeInactive: true));
    }

    public function store(CreateProductRequest $request, CreateProductAction $action): JsonResponse
    {
        return ProductResource::make(
            $action->execute(CreateProductDTO::fromArray($request->validated()))
        )->response()->setStatusCode(201);
    }

    public function show(int $id, GetProductByIdAction $action): ProductResource
    {
        return ProductResource::make($action->execute($id, includeInactive: true));
    }

    public function update(int $id, UpdateProductRequest $request, UpdateProductAction $action): ProductResource
    {
        return ProductResource::make(
            $action->execute($id, UpdateProductDTO::fromArray($request->validated()))
        );
    }

    public function destroy(int $id, DeleteProductAction $action): Response
    {
        $action->execute($id);

        return response()->noContent();
    }
}
