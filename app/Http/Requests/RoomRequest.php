<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="RoomRequest",
 *     required={"number", "room_type_id", "floor"},
 *     @OA\Property(property="number", type="string", maxLength=10),
 *     @OA\Property(property="room_type_id", type="integer", format="int64"),
 *     @OA\Property(property="floor", type="integer"),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive", "maintenance"}),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="amenities", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="is_maintenance", type="boolean")
 * )
 */
class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-rooms');
    }

    public function rules(): array
    {
        $rules = [
            'number' => ['required', 'string', 'max:10'],
            'type_id' => ['required', 'exists:room_types,id'],
            'floor' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(['active', 'inactive', 'maintenance'])],
            'description' => ['nullable', 'string', 'max:1000'],
            'price_per_night' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string', 'max:100'],
            'is_maintenance' => ['boolean'],
        ];

        // If updating an existing room, make the number unique except for the current room
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['number'][] = Rule::unique('rooms')->ignore($this->room);
        } else {
            $rules['number'][] = 'unique:rooms';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'number.required' => 'Room number is required',
            'number.unique' => 'This room number is already taken',
            'type_id.required' => 'Room type is required',
            'type_id.exists' => 'Selected room type does not exist',
            'floor.required' => 'Floor number is required',
            'floor.integer' => 'Floor must be a number',
            'floor.min' => 'Floor must be at least 1',
            'status.required' => 'Room status is required',
            'status.in' => 'Invalid room status',
            'price_per_night.required' => 'Price per night is required',
            'price_per_night.numeric' => 'Price must be a number',
            'price_per_night.min' => 'Price cannot be negative',
            'capacity.required' => 'Room capacity is required',
            'capacity.integer' => 'Capacity must be a number',
            'capacity.min' => 'Capacity must be at least 1',
            'amenities.array' => 'Amenities must be a list',
        ];
    }
} 