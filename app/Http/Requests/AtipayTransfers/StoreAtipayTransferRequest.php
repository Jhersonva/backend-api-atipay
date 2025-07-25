<?php

namespace App\Http\Requests\AtipayTransfers;

use Illuminate\Foundation\Http\FormRequest;

class StoreAtipayTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receiver_id' => 'required|exists:users,id|different:sender_id',
            'amount' => 'required|integer|min:1',
            'type' => 'required|in:investment,store',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'sender_id' => auth('api')->id(),
        ]);
    }

}
