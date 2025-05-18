<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillVerification extends Model
{
    protected $fillable = [
        'staff_skill_id',
        'verified_by',
        'notes',
        'verification_date',
        'next_verification_date',
    ];

    protected $casts = [
        'verification_date' => 'date',
        'next_verification_date' => 'date',
    ];

    public function staffSkill(): BelongsTo
    {
        return $this->belongsTo(StaffSkill::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isExpired(): bool
    {
        if (!$this->next_verification_date) {
            return false;
        }

        return $this->next_verification_date->isPast();
    }

    public function scopeExpired($query)
    {
        return $query->where('next_verification_date', '<=', now());
    }

    public function scopeByVerifier($query, int $userId)
    {
        return $query->where('verified_by', $userId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('verification_date', '>=', now()->subDays($days));
    }
} 