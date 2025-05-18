<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="MaintenanceCategory",
 *     required={"name", "description"},
 *     @OA\Property(property="id", type="integer", format="int64", readOnly=true),
 *     @OA\Property(property="name", type="string", maxLength=255),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="estimated_duration", type="integer", description="Estimated duration in minutes"),
 *     @OA\Property(property="required_skills", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="required_items", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="priority_level", type="integer", minimum=1, maximum=5),
 *     @OA\Property(property="is_recurring", type="boolean"),
 *     @OA\Property(property="recurrence_pattern", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class MaintenanceCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'estimated_duration',
        'required_skills',
        'required_items',
        'priority_level',
        'is_recurring',
        'recurrence_pattern',
    ];

    protected $casts = [
        'required_skills' => 'array',
        'required_items' => 'array',
        'estimated_duration' => 'integer',
        'priority_level' => 'integer',
        'is_recurring' => 'boolean',
    ];

    public function maintenanceTasks(): HasMany
    {
        return $this->hasMany(MaintenanceTask::class, 'category_id');
    }

    public function getRequiredInventoryItems(): array
    {
        return collect($this->required_items)->map(function ($item) {
            return [
                'item_id' => $item['id'],
                'quantity' => $item['quantity'],
            ];
        })->all();
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeByPriority($query, int $level)
    {
        return $query->where('priority_level', $level);
    }
} 