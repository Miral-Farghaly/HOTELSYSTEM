<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'room_id' => 'required|exists:rooms,id',
            'category_id' => 'required|exists:maintenance_categories,id',
            'assigned_to' => 'nullable|exists:users,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
            'actual_duration' => 'nullable|integer|min:1',
            'items_used' => 'nullable|array',
            'items_used.*.id' => 'required|exists:maintenance_inventory,id',
            'items_used.*.quantity' => 'required|integer|min:1',
            'parent_task_id' => 'nullable|exists:maintenance_tasks,id',
            'recurrence_rule' => 'nullable|string',
        ];

        if ($this->isMethod('PUT')) {
            $rules['scheduled_date'] = 'required|date';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'room_id.required' => 'The room is required.',
            'room_id.exists' => 'The selected room does not exist.',
            'category_id.required' => 'The maintenance category is required.',
            'category_id.exists' => 'The selected maintenance category does not exist.',
            'assigned_to.exists' => 'The selected staff member does not exist.',
            'scheduled_date.required' => 'The scheduled date is required.',
            'scheduled_date.after_or_equal' => 'The scheduled date must be today or a future date.',
            'status.required' => 'The status is required.',
            'status.in' => 'Invalid status selected.',
            'actual_duration.min' => 'The actual duration must be at least 1 minute.',
            'items_used.*.id.exists' => 'One or more selected inventory items do not exist.',
            'items_used.*.quantity.min' => 'The quantity for each used item must be at least 1.',
            'parent_task_id.exists' => 'The selected parent task does not exist.',
        ];
    }
} 