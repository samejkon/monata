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
            'checkin_at' => ['required', 'date'],
            'checkout_at' => ['required', 'date', 'after:checkin_at'],
        ];
    }
}
