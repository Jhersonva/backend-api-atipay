<?php

namespace App\Http\Controllers\Api\ProductCategories;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCategories\StoreProductCategoryRequest;
use App\Http\Requests\ProductCategories\UpdateProductCategoryRequest;
use App\Services\ProductCategoriesService;
use Illuminate\Http\JsonResponse;

class ProductCategoriesController extends Controller
{
    protected ProductCategoriesService $productCategoriesService;

    public function __construct(ProductCategoriesService $productCategoriesService)
    {
        $this->productCategoriesService = $productCategoriesService;
    }

    /**
     * Obtener todas las categorías de productos.
     */
    public function index(): JsonResponse
    {
        $categories = $this->productCategoriesService->getAll();
        return response()->json($categories);
    }

    /**
     * Obtener una categoría por ID.
     */
    public function show($id): JsonResponse
    {
        $category = $this->productCategoriesService->findById($id);
        return response()->json($category);
    }

    /**
     * Crear una nueva categoría.
     */
    public function store(StoreProductCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $category = $this->productCategoriesService->store($data);

        return response()->json($category, 201);
    }

    /**
     * Actualizar una categoría existente.
     */
    public function update(UpdateProductCategoryRequest $request, $id): JsonResponse
    {
        $data = $request->validated();
        $category = $this->productCategoriesService->update($id, $data);

        return response()->json($category);
    }

    /**
     * Eliminar una categoría.
     */
    public function destroy($id): JsonResponse
    {
        $success = $this->productCategoriesService->delete($id);

        if ($success) {
            return response()->json(['message' => 'Categoría eliminada exitosamente.']);
        }

        return response()->json(['message' => 'Categoría no encontrada.'], 404);
    }
}
