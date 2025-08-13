<?php

namespace App\Http\Requests\AtipayRecharges;

use Illuminate\Foundation\Http\FormRequest;

class StoreAtipayRechargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_names' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:yape,plin,transferencia_bancaria,transferencia_electronica',
            'proof_image' => 'required|image|max:2048',
        ];
    }
}
