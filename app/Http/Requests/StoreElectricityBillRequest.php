<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreElectricityBillRequest extends FormRequest
{
    protected function getRedirectUrl(): string
    {
        return route('admin.dashboard').'#settings';
    }

    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'period' => ['required', 'date_format:Y-m'],
            'total_amount' => ['required', 'numeric', 'min:0', 'max:10000000'],
        ];
    }
}
