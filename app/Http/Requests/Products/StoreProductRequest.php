<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:products,name',
            'product_categories' => 'required|exists:product_categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'required_points' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
            'type' => 'required|in:unidad,paquete,kilos,ropa',
            'image' => 'nullable|image|max:2048',
        ];
    }
}
