<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Services\ProductsService;
use Illuminate\Http\JsonResponse;

class ProductsController extends Controller
{
    protected ProductsService $productsService;

    public function __construct(ProductsService $productsService)
    {
        $this->productsService = $productsService;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->productsService->getAll());
    }

    public function show($id): JsonResponse
    {
        return response()->json($this->productsService->findById($id));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productsService->store($request->validated());
        return response()->json($product, 201);
    }

    public function update(UpdateProductRequest $request, $id): JsonResponse
    {
        $product = $this->productsService->update($id, $request->validated());
        return response()->json($product);
    }

    public function destroy($id): JsonResponse
    {
        $this->productsService->delete($id);
        return response()->json(['message' => 'Producto eliminado exitosamente.']);
    }
}