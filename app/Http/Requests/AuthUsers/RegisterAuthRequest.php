<?php

namespace App\Http\Requests\AuthUsers;

use Illuminate\Foundation\Http\FormRequest;

class RegisterAuthRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'username' => ['required', 'string', 'unique:users,username'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'reference_code' => ['nullable', 'string', 'exists:users,reference_code'],
        ];
    }

    public function messages()
    {
        return [
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe incluir una mayúscula, minúscula, número y carácter especial.',
            'username.unique' => 'El nombre de usuario ya está en uso.',
            'email.unique' => 'El correo electrónico ya está registrado.',

        ];
    }
}
