<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectDeviceDeclarationRequest extends FormRequest
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
            'reason' => ['required', 'string', 'max:255'],
        ];
    }
}
