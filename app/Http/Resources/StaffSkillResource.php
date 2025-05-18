<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffSkillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'skill_name' => $this->skill_name,
            'level' => $this->level,
            'description' => $this->description,
            'certifications' => $this->certifications,
            'acquired_date' => $this->acquired_date,
            'expiry_date' => $this->expiry_date,
            'is_expired' => $this->isExpired(),
            'needs_verification' => $this->needsVerification(),
            'verifications' => $this->whenLoaded('verifications', function () {
                return $this->verifications->map(function ($verification) {
                    return [
                        'id' => $verification->id,
                        'verified_by' => [
                            'id' => $verification->verifier->id,
                            'name' => $verification->verifier->name,
                        ],
                        'notes' => $verification->notes,
                        'verification_date' => $verification->verification_date,
                        'next_verification_date' => $verification->next_verification_date,
                        'is_expired' => $verification->isExpired(),
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 