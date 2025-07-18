<?php

namespace App\Http\Requests\Commissions;

use Illuminate\Foundation\Http\FormRequest;

class CommissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'referred_user' => 'required|exists:users,id',
            'level' => 'required|integer|min:1',
            'points_earned' => 'required|integer|min:0',
            'total_amount' => 'required|numeric|min:0',
            'month' => 'required|date_format:Y-m-d',
            'generation_date' => 'nullable|date_format:Y-m-d H:i:s',
        ];
    }
}
