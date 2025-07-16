<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255|unique:products,name,' . $this->route('id'),
            'product_categories' => 'sometimes|exists:product_categories,id',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'required_points' => 'sometimes|integer|min:0',
            'stock' => 'sometimes|integer|min:0',
            'status' => 'sometimes|in:active,inactive',
            'type' => 'sometimes|in:unidad,paquete,kilos,ropa',
            'image' => 'nullable|image|max:2048',
        ];
    }
}
