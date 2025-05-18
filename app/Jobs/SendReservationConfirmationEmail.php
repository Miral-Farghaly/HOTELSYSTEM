<?php

namespace App\Jobs;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationConfirmation;

class SendReservationConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reservation;
    public $tries = 3;
    public $maxExceptions = 3;
    public $timeout = 30;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function handle(): void
    {
        Mail::to($this->reservation->user->email)
            ->send(new ReservationConfirmation($this->reservation));
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('Failed to send reservation confirmation email', [
            'reservation_id' => $this->reservation->id,
            'user_id' => $this->reservation->user_id,
            'error' => $exception->getMessage()
        ]);
    }

    public function retryUntil(): \DateTime
    {
        return now()->addHours(2);
    }
} 