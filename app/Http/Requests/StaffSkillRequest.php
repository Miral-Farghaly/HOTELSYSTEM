<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StaffSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'skill_name' => 'required|string|max:255',
            'level' => 'required|string|in:beginner,intermediate,advanced,expert',
            'description' => 'nullable|string',
            'certifications' => 'nullable|array',
            'certifications.*.name' => 'required|string',
            'certifications.*.issuer' => 'required|string',
            'certifications.*.date' => 'required|date',
            'certifications.*.expiry_date' => 'nullable|date|after:certifications.*.date',
            'acquired_date' => 'nullable|date|before_or_equal:today',
            'expiry_date' => 'nullable|date|after:acquired_date',
        ];

        if ($this->isMethod('PUT')) {
            $rules['user_id'] = 'exists:users,id';
            $rules['skill_name'] = 'string|max:255';
            $rules['level'] = 'string|in:beginner,intermediate,advanced,expert';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'The user ID is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'skill_name.required' => 'The skill name is required.',
            'level.required' => 'The skill level is required.',
            'level.in' => 'The skill level must be one of: beginner, intermediate, advanced, expert.',
            'certifications.*.name.required' => 'The certification name is required.',
            'certifications.*.issuer.required' => 'The certification issuer is required.',
            'certifications.*.date.required' => 'The certification date is required.',
            'certifications.*.expiry_date.after' => 'The certification expiry date must be after the certification date.',
            'acquired_date.before_or_equal' => 'The acquired date cannot be in the future.',
            'expiry_date.after' => 'The expiry date must be after the acquired date.',
        ];
    }
} 