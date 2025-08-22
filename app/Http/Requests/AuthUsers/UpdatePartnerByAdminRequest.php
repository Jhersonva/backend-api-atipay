<?php

namespace App\Http\Requests\AuthUsers;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class UpdatePartnerByAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check() && auth('api')->user()->role->name === User::ROLE_ADMIN;
    }

    public function rules(): array
    {
        return [
            'status'   => 'sometimes|in:active,inactive',
            'password' => 'sometimes|string|min:6|confirmed',
        ];
    }
}
