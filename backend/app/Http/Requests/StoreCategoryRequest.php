<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'A kategória neve kötelező.',
            'name.max'      => 'A név legfeljebb 255 karakter lehet.',
            'name.unique'   => 'Ez a kategórianév már létezik.',
        ];
    }
}
