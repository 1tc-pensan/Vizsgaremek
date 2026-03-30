<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'witnesses'  => $this->witnesses  !== '' ? $this->witnesses  : null,
            'latitude'   => $this->latitude   !== '' ? $this->latitude   : null,
            'longitude'  => $this->longitude  !== '' ? $this->longitude  : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'category_id'  => ['sometimes', 'exists:categories,id'],
            'title'        => ['sometimes', 'string', 'max:255'],
            'description'  => ['sometimes', 'string'],
            'latitude'     => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'    => ['nullable', 'numeric', 'between:-180,180'],
            'date'         => ['sometimes', 'date', 'before_or_equal:today'],
            'witnesses'    => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists'   => 'A kiválasztott kategória nem létezik.',
            'title.max'            => 'A cím legfeljebb 255 karakter lehet.',
            'latitude.numeric'     => 'A szélességi fok csak szám lehet.',
            'latitude.between'     => 'A szélességi fok -90 és 90 közé kell essen.',
            'longitude.numeric'    => 'A hosszúsági fok csak szám lehet.',
            'longitude.between'    => 'A hosszúsági fok -180 és 180 közé kell essen.',
            'date.date'            => 'Érvényes dátumot adj meg.',
            'date.before_or_equal' => 'Az esemény dátuma nem lehet jövőbeli.',
            'witnesses.integer'    => 'A tanúk száma csak egész szám lehet.',
            'witnesses.min'        => 'A tanúk száma nem lehet negatív.',
        ];
    }
}
