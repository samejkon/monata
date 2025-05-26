<?php

namespace App\Http\Resources;

use Carbon\Carbon;
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
            'room_id' => $this->room_id,
            'checkin_at' => Carbon::parse($this->checkin_at)->format('Y-m-d H:i'),
            'checkout_at' => Carbon::parse($this->checkout_at)->format('Y-m-d H:i'),
            'price_per_day' => $this->price_per_day,
            'id' => $this->id,
        ];
    }
}
