<?php

namespace App\Services;

use App\Models\Product_Categories;
use Illuminate\Database\Eloquent\Collection;

class ProductCategoriesService
{
    public function getAll(): Collection
    {
        return Product_Categories::all();
    }

    public function getById(int $id): ?Product_Categories
    {
        return Product_Categories::find($id);
    }

    public function create(array $data): Product_Categories
    {
        return Product_Categories::create($data);
    }

    public function update(int $id, array $data): ?Product_Categories
    {
        $category = Product_Categories::find($id);
        if ($category) {
            $category->update($data);
        }
        return $category;
    }

    public function delete(int $id): bool
    {
        $category = Product_Categories::find($id);
        if ($category) {
            return $category->delete();
        }
        return false;
    }
}
