<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function isAdmin()
    {
        return $this->role === 'manager';
    }

    public function isReceptionist()
    {
        return $this->role === 'receptionist';
    }

    public function isGuest()
    {
        return $this->role === 'guest';
    }

    public function staffSkills(): HasMany
    {
        return $this->hasMany(StaffSkill::class);
    }

    public function verifiedSkills(): HasMany
    {
        return $this->hasMany(SkillVerification::class, 'verified_by');
    }

    public function hasSkill(string $skillName, string $level = null): bool
    {
        $query = $this->staffSkills()
            ->where('skill_name', $skillName)
            ->active();

        if ($level) {
            $query->where('level', $level);
        }

        return $query->exists();
    }

    public function hasRequiredSkills(array $requiredSkills): bool
    {
        foreach ($requiredSkills as $skill) {
            if (!$this->hasSkill($skill['name'], $skill['level'] ?? null)) {
                return false;
            }
        }

        return true;
    }
}
