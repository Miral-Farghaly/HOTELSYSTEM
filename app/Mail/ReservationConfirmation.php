<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function build()
    {
        return $this->view('emails.reservations.confirmation')
            ->subject('Your Hotel Reservation Confirmation')
            ->with([
                'reservation' => $this->reservation,
                'user' => $this->reservation->user,
                'room' => $this->reservation->room,
                'qrCode' => $this->generateQrCode(),
            ]);
    }

    protected function generateQrCode(): string
    {
        return \QrCode::size(300)
            ->generate(route('reservations.verify', $this->reservation->confirmation_code));
    }
} 