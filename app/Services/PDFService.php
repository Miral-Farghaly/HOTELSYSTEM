<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;

class PDFService
{
    protected $dompdf;

    public function __construct()
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);

        $this->dompdf = new Dompdf($options);
    }

    public function generateBookingConfirmation(Booking $booking): string
    {
        $html = View::make('pdfs.booking_confirmation', [
            'booking' => $booking,
            'hotel_name' => config('app.name'),
            'logo_path' => public_path('images/logo.png'),
        ])->render();

        return $this->generatePDF($html, "booking_confirmation_{$booking->id}.pdf");
    }

    public function generateInvoice(Payment $payment): string
    {
        $html = View::make('pdfs.invoice', [
            'payment' => $payment,
            'booking' => $payment->booking,
            'user' => $payment->booking->user,
            'hotel_details' => [
                'name' => config('app.name'),
                'address' => config('hotel.address'),
                'phone' => config('hotel.phone'),
                'email' => config('hotel.email'),
                'tax_number' => config('hotel.tax_number'),
            ],
        ])->render();

        return $this->generatePDF($html, "invoice_{$payment->id}.pdf");
    }

    public function generateReceipt(Payment $payment): string
    {
        $html = View::make('pdfs.receipt', [
            'payment' => $payment,
            'booking' => $payment->booking,
            'user' => $payment->booking->user,
            'hotel_name' => config('app.name'),
        ])->render();

        return $this->generatePDF($html, "receipt_{$payment->id}.pdf");
    }

    protected function generatePDF(string $html, string $filename): string
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();

        $output = $this->dompdf->output();
        $path = "pdfs/{$filename}";

        Storage::put("public/{$path}", $output);

        return $path;
    }

    public function downloadPDF(string $path): string
    {
        return Storage::download("public/{$path}");
    }

    public function deletePDF(string $path): bool
    {
        return Storage::delete("public/{$path}");
    }
} 