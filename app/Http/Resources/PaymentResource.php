<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transaction_id' => $this->transaction_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'booking_id' => $this->booking_id,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'refund_reason' => $this->when($this->status === 'refunded', $this->refund_reason),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
} 