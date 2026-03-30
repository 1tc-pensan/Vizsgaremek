<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vote_type' => ['required', 'in:up,down'],
        ];
    }

    public function messages(): array
    {
        return [
            'vote_type.required' => 'A szavazat típusa kötelező.',
            'vote_type.in'       => 'Érvénytelen szavazat típus.',
        ];
    }
}
