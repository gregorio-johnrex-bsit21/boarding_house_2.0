<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeviceCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'available' => ['required', 'boolean'],
            'reason' => ['nullable', 'string', 'max:255'],
            'total_units' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }
}