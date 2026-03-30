<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'A név megadása kötelező.',
            'name.max'          => 'A név legfeljebb 255 karakter lehet.',
            'email.required'    => 'Az e-mail cím megadása kötelező.',
            'email.email'       => 'Érvényes e-mail címet adj meg.',
            'email.unique'      => 'Ez az e-mail cím már foglalt.',
            'password.required' => 'A jelszó megadása kötelező.',
            'password.min'      => 'A jelszó legalább 8 karakter legyen.',
            'password.confirmed'=> 'A két jelszó nem egyezik.',
        ];
    }
}
