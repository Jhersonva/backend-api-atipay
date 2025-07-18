<?php

namespace App\Http\Controllers\ProductCategories;


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
    public function show(int $id): JsonResponse
    {
        $category = $this->productCategoriesService->getById($id);

        if (!$category) {
            return response()->json(['message' => 'Categoría no encontrada.'], 404);
        }

        return response()->json($category);
    }

    /**
     * Crear una nueva categoría.
     */
    public function store(StoreProductCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $category = $this->productCategoriesService->create($data);

        return response()->json($category, 201);
    }

    /**
     * Actualizar una categoría existente.
     */
    public function update(UpdateProductCategoryRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        $category = $this->productCategoriesService->update($id, $data);

        if (!$category) {
            return response()->json(['message' => 'Categoría no encontrada.'], 404);
        }

        return response()->json($category);
    }

    /**
     * Eliminar una categoría.
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->productCategoriesService->delete($id);

        if (!$deleted) {
            return response()->json(['message' => 'Categoría no encontrada.'], 404);
        }

        return response()->json(['message' => 'Categoría eliminada exitosamente.']);
    }
}
