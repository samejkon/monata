<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'guest_name' => $this->guest_name,
            'guest_email' => $this->guest_email,
            'guest_phone' => $this->guest_phone,
            'checkin' => $this->checkin,
            'checkout' => $this->checkout,
            'deposit' => $this->deposit,
            'total_payment' => $this->total_payment,
            'stauts' => $this->stauts,
            'booking_details' => BookingDetailResource::collection($this->bookingDetails),
        ];
    }
}
