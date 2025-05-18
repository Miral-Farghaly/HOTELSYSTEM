<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\PDFService;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingConfirmation;

class GenerateBookingDocuments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $booking;
    protected $payment;
    public $tries = 3;
    public $timeout = 180;

    public function __construct(Booking $booking, ?Payment $payment = null)
    {
        $this->booking = $booking;
        $this->payment = $payment;
    }

    public function handle(PDFService $pdfService): void
    {
        try {
            // Generate booking confirmation
            $confirmationPath = $pdfService->generateBookingConfirmation($this->booking);

            // Generate invoice if payment exists
            $invoicePath = null;
            if ($this->payment) {
                $invoicePath = $pdfService->generateInvoice($this->payment);
                $receiptPath = $pdfService->generateReceipt($this->payment);
            }

            // Send email with attachments
            Mail::to($this->booking->user->email)
                ->send(new BookingConfirmation($this->booking, [
                    'confirmation' => $confirmationPath,
                    'invoice' => $invoicePath,
                    'receipt' => $receiptPath ?? null,
                ]));

            // Update booking status
            $this->booking->update(['documents_generated' => true]);

        } catch (\Exception $e) {
            \Log::error('Failed to generate booking documents', [
                'error' => $e->getMessage(),
                'booking_id' => $this->booking->id,
                'payment_id' => $this->payment?->id
            ]);

            $this->release(60); // Retry after 1 minute
        }
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('Booking documents generation job failed', [
            'error' => $exception->getMessage(),
            'booking_id' => $this->booking->id
        ]);

        // Notify admin about the failure
        Mail::to(config('mail.admin_address'))->send(new \App\Mail\DocumentGenerationFailed($this->booking));
    }
} 