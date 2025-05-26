<?php

namespace App\Http\Requests\Booking;

use App\Base\FormRequest;

class UpdateBookingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['required', 'email', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:20'],
            'check_in' => ['nullable', 'date'],
            'check_out' => ['nullable', 'date'],
            'deposit' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'total_payment' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'integer'],

            'booking_details' => ['required', 'array'],
            'booking_details.*.id' => ['nullable', 'integer', 'exists:booking_details,id'],
            'booking_details.*.room_id' => ['required', 'integer', 'exists:rooms,id'],
            'booking_details.*.checkin_at' => ['required', 'date'],
            'booking_details.*.checkout_at' => ['required', 'date'],
        ];
    }
}
