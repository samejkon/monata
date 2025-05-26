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
            'id',
            'room_id',
            'booking_id',
            'room_type_id',
            'checkin_at',
            'checkout_at',
            'price_per_day'
        ];
    }
}
