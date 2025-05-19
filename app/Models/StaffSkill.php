<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @OA\Schema(
 *     schema="StaffSkill",
 *     required={"user_id", "skill_name", "level"},
 *     @OA\Property(property="id", type="integer", format="int64", readOnly=true),
 *     @OA\Property(property="user_id", type="integer", format="int64"),
 *     @OA\Property(property="skill_name", type="string", maxLength=255),
 *     @OA\Property(property="level", type="string", enum={"beginner", "intermediate", "advanced", "expert"}),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="certifications", type="array", @OA\Items(type="string"), nullable=true),
 *     @OA\Property(property="acquired_date", type="string", format="date", nullable=true),
 *     @OA\Property(property="expiry_date", type="string", format="date", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true)
 * )
 */
class StaffSkill extends Model
{
    protected $fillable = [
        'user_id',
        'skill_name',
        'level',
        'description',
        'certifications',
        'acquired_date',
        'expiry_date',
    ];

    protected $casts = [
        'certifications' => 'array',
        'acquired_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(SkillVerification::class);
    }

    public function latestVerification()
    {
        return $this->verifications()->latest('verification_date')->first();
    }

    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isPast();
    }

    public function needsVerification(): bool
    {
        $latestVerification = $this->latestVerification();
        
        if (!$latestVerification || !$latestVerification->next_verification_date) {
            return false;
        }

        return $latestVerification->next_verification_date->isPast();
    }

    public function scopeBySkill($query, string $skillName)
    {
        return $query->where('skill_name', $skillName);
    }

    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expiry_date')
                ->orWhere('expiry_date', '>', now());
        });
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<=', now());
    }

    public function scopeNeedsVerification($query)
    {
        return $query->whereHas('verifications', function ($q) {
            $q->where('next_verification_date', '<=', now());
        });
    }
} 