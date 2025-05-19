<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'price' => $this->price,
            'description' => $this->description,
            'available' => $this->available,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    public function with(Request $request): array
    {
        return [
            'status' => 'success',
            'version' => '1.0',
        ];
    }
} 