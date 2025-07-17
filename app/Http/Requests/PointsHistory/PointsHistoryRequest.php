<?php

namespace App\Http\Requests\PointsHistory;

use Illuminate\Foundation\Http\FormRequest;

class PointsHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer|min:1',
            'source' => 'required|in:compra,afiliado,bono',
            'note'   => 'nullable|string',
        ];
    }
}
