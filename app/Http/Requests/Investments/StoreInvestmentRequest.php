<?php

namespace App\Http\Requests\Investments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
class StoreInvestmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'promotion_id' => ['required', 'exists:promotions,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'], // 2MB máx
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = auth('api')->user();
            $amount = $this->input('amount');

            if ($user->atipay_investment_balance < $amount) {
                $validator->errors()->add('amount', 'Saldo insuficiente para invertir esa cantidad.');
            }

            if ($validator->errors()->any()) {
                throw new ValidationException($validator, response()->json([
                    'message' => 'Error de validación.',
                    'errors' => $validator->errors()
                ], 422));
            }
        });
    }
}
