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
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:yape,plin',
            'type_usage' => 'required|in:investment,store',
            'proof_image' => 'required|image|max:2048', // o 'file' si se sube un archivo
        ];
    }
}
