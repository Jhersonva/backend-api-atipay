<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductsService
{
    public function getAll()
    {
        return Product::with('category')->get();
    }

    public function findById($id)
    {
        return Product::with('category')->findOrFail($id);
    }

    public function store(array $data)
    {
        if (isset($data['image'])) {
            $data['image'] = $data['image']->store('products', 'public');
        }

        return Product::create($data)->load('category');
    }

    public function update($id, array $data)
    {
        $product = Product::findOrFail($id);

        if (isset($data['image'])) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $data['image']->store('products', 'public');
        }

        $product->update($data);
        return $product->load('category');
    }

    public function delete($id): bool
    {
        $product = Product::findOrFail($id);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        return $product->delete();
    }
}