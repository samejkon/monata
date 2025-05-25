<?php

namespace App\Http\Requests\Room;

use Illuminate\Validation\Rule;
use App\Base\FormRequest;

class SearchRoomRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'room_type_id' => [
                'nullable',
                'integer',
                'exists:room_types,id',
            ],
            'price_from' => [
                'nullable',
                'numeric',
                'min:0',
                'lte:price_to'
            ],
            'price_to' => [
                'nullable',
                'numeric',
                'min:0',
                'gte:price_from'
            ],
            'status' => [
                'nullable',
                'integer',
                Rule::enum(RoomStatus::class),
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:3',
            ],
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }
}
