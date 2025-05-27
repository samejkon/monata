<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'room_id' => $this->room_id,
            'booking_id' => $this->booking_id,
            'room_id' => $this->room_id,
            'checkin_at' => $this->checkin_at,
            'checkout_at' => $this->checkout_at,
            'price_per_day' => $this->price_per_day,
            'id' => $this->id,
        ];
    }
}
