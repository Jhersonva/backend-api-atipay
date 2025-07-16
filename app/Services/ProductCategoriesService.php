<?php

namespace App\Services;

use App\Models\Product_Categories;

class ProductCategoriesService
{
    /**
     * Obtener todas las categorías de productos.
     */
    public function getAll()
    {
        return Product_Categories::all();
    }

    /**
     * Buscar una categoría por ID.
     */
    public function findById($id)
    {
        return Product_Categories::findOrFail($id);
    }

    /**
     * Crear una nueva categoría.
     */
    public function store(array $data)
    {
        return Product_Categories::create([
            'name' => $data['name'],
        ]);
    }

    /**
     * Actualizar una categoría existente.
     */
    public function update($id, array $data)
    {
        $category = Product_Categories::findOrFail($id);

        $category->update([
            'name' => $data['name'] ?? $category->name,
        ]);

        return $category;
    }

    /**
     * Eliminar una categoría.
     */
    public function delete($id): bool
    {
        $category = Product_Categories::findOrFail($id);
        return $category->delete();
    }
}
