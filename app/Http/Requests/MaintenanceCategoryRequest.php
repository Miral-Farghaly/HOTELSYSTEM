<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'estimated_duration' => 'required|integer|min:1',
            'required_skills' => 'nullable|array',
            'required_skills.*' => 'string',
            'required_items' => 'nullable|array',
            'required_items.*.id' => 'required|exists:maintenance_inventory,id',
            'required_items.*.quantity' => 'required|integer|min:1',
            'priority_level' => 'required|integer|min:1|max:5',
            'is_recurring' => 'boolean',
            'recurrence_pattern' => 'nullable|string|required_if:is_recurring,true',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'description.required' => 'The category description is required.',
            'estimated_duration.required' => 'The estimated duration is required.',
            'estimated_duration.min' => 'The estimated duration must be at least 1 minute.',
            'required_items.*.id.exists' => 'One or more selected inventory items do not exist.',
            'required_items.*.quantity.min' => 'The quantity for each required item must be at least 1.',
            'priority_level.required' => 'The priority level is required.',
            'priority_level.min' => 'The priority level must be between 1 and 5.',
            'priority_level.max' => 'The priority level must be between 1 and 5.',
            'recurrence_pattern.required_if' => 'The recurrence pattern is required when the task is recurring.',
        ];
    }
} 