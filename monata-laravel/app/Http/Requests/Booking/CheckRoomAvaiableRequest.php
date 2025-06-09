<?php

namespace App\Http\Requests\Booking;

use App\Base\FormRequest;

class CheckRoomAvaiableRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'roomType' => ['nullable', 'integer'],
            'roomId' => ['nullable', 'integer'],
            'checkin_at' => ['required', 'date_format:Y-m-d H:i', 'after_or_equal:today', 'before:checkout_at'],
            'checkout_at' => ['required', 'date_format:Y-m-d H:i', 'after:checkin_at'],
        ];
    }

    /**
     * Custom error messages for the request.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'checkin_at.required' => 'Check-in date is required.',
            'checkout_at.required' => 'Check-out date is required.',
            'checkout_at.after' => 'Check-out date must be after check-in date.',
        ];
    }
}
