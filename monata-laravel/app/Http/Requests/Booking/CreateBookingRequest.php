<?php

namespace App\Http\Requests\Booking;

use App\Base\FormRequest;
use Illuminate\Validation\Rule;

class CreateBookingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['required', 'email', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:20'],
            'check_in' => ['nullable', 'date', 'after_or_equal:today'],
            'check_out' => ['nullable', 'date', 'after:check_in'],
            'deposit' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'total_payment' => ['nullable', 'numeric', 'min:0'],

            'booking_details' => ['required', 'array'],
            'booking_details.*.room_id' => ['required', 'integer', 'exists:rooms,id', 'distinct'],
            'booking_details.*.checkin_at' => ['required', 'date', 'after_or_equal:today'],
            'booking_details.*.checkout_at' => ['required', 'date', 'after:checkin_at'],
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'booking_details.*.room_id' => 'Room does not exist.',
        ];
    }
}
