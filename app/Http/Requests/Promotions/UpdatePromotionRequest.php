<?php

namespace App\Http\Requests\Promotions;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'percentaje' => 'sometimes|numeric|min:0|max:100',
            'duration_months' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:active,inactive',
        ];
    }
}
