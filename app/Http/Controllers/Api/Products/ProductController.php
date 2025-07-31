<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Exception;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Listar todos los productos activos
     */
    public function index(): JsonResponse
    {
        $products = $this->productService->getAll();
        return response()->json($products);
    }

    /**
     * Crear un nuevo producto (producto o curso)
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->store($request->validated());
            return response()->json([
                'message' => 'Producto creado exitosamente.',
                'data' => $product
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error al crear producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un producto por ID (incluye curso si aplica)
     */
    public function show($id): JsonResponse
    {
        $product = $this->productService->getById($id);
        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado.'], 404);
        }
        return response()->json($product);
    }

    /**
     * Actualizar un producto (maneja también curso)
     */
    public function update(UpdateProductRequest $request, $id): JsonResponse
    {

        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado.'], 404);
        }

        try {
            $updated = $this->productService->update($product, $request);
            return response()->json([
                'message' => 'Producto actualizado exitosamente.',
                'data' => $updated
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar producto: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Eliminar un producto y su curso si aplica
     */
    public function destroy($id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado.'], 404);
        }

        try {
            $this->productService->delete($product);
            return response()->json(['message' => 'Producto eliminado exitosamente.']);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error al eliminar producto: ' . $e->getMessage()
            ], 500);
        }
    }
}