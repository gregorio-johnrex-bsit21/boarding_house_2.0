<?php

namespace App\Http\Requests;

use App\Models\DeviceCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDeviceDeclarationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'tenant';
    }

    public function rules(): array
    {
        return [
            'catalog_device_id' => ['nullable', 'integer', 'exists:device_catalog,id'],
            'name' => ['required_without:catalog_device_id', 'nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $catalogId = $this->input('catalog_device_id');
                if ($catalogId !== null && $catalogId !== '') {
                    $catalogDevice = DeviceCatalog::find($catalogId);
                    if ($catalogDevice && stripos($catalogDevice->name, 'charging') !== false) {
                        $validator->errors()->add('catalog_device_id', 'This device is not allowed.');
                    }
                }

                if (stripos((string) $this->input('name'), 'charging') !== false) {
                    $validator->errors()->add('name', 'This device name is not allowed.');
                }
            },
        ];
    }
}
