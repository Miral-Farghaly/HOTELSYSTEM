<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|max:50|unique:maintenance_inventory,sku',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:20',
            'minimum_quantity' => 'required|numeric|min:0',
            'reorder_point' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'supplier_info' => 'nullable|array',
        ];

        if ($this->isMethod('PUT')) {
            $rules['sku'] = 'required|string|max:50|unique:maintenance_inventory,sku,' . $this->route('item')->id;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The item name is required.',
            'sku.required' => 'The SKU is required.',
            'sku.unique' => 'This SKU is already in use.',
            'quantity.required' => 'The quantity is required.',
            'quantity.min' => 'The quantity cannot be negative.',
            'unit.required' => 'The unit of measurement is required.',
            'minimum_quantity.required' => 'The minimum quantity is required.',
            'minimum_quantity.min' => 'The minimum quantity cannot be negative.',
            'reorder_point.required' => 'The reorder point is required.',
            'reorder_point.min' => 'The reorder point cannot be negative.',
        ];
    }
} 