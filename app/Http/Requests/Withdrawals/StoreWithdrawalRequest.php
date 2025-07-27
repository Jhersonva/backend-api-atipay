<?php

namespace App\Http\Requests\Withdrawals;

use Illuminate\Foundation\Http\FormRequest;

class StoreWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:yape,plin',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth('api')->id(),
        ]);
    }
}
