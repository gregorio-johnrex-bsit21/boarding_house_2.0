<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDeviceCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:255'],
            'total_units' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (stripos((string) $this->input('name'), 'charging') !== false) {
                    $validator->errors()->add('name', 'This device name is not allowed.');
                }
            },
        ];
    }
}