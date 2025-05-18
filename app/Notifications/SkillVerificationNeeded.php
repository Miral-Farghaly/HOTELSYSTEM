<?php

namespace App\Notifications;

use App\Models\StaffSkill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SkillVerificationNeeded extends Notification implements ShouldQueue
{
    use Queueable;

    protected StaffSkill $skill;

    public function __construct(StaffSkill $skill)
    {
        $this->skill = $skill;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $lastVerification = $this->skill->latestVerification();

        return (new MailMessage)
            ->subject('Skill Verification Required')
            ->line("A staff skill requires verification:")
            ->line("Staff Member: {$this->skill->user->name}")
            ->line("Skill: {$this->skill->skill_name}")
            ->line("Level: {$this->skill->level}")
            ->line("Last Verified: {$lastVerification?->verification_date->format('Y-m-d')}")
            ->line("Verification Due: {$lastVerification?->next_verification_date->format('Y-m-d')}")
            ->action('Verify Skill', url("/staff/skills/{$this->skill->id}/verify"))
            ->line('Please review and verify the skill level.');
    }

    public function toArray($notifiable): array
    {
        $lastVerification = $this->skill->latestVerification();

        return [
            'skill_id' => $this->skill->id,
            'user_id' => $this->skill->user_id,
            'user_name' => $this->skill->user->name,
            'skill_name' => $this->skill->skill_name,
            'level' => $this->skill->level,
            'last_verified' => $lastVerification?->verification_date->format('Y-m-d'),
            'verification_due' => $lastVerification?->next_verification_date->format('Y-m-d'),
        ];
    }
} 