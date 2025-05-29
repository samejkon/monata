<?php

namespace App\Http\Requests\RoomType;

use App\Base\FormRequest;

class UpdateRoomTypeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'sometimes', 'max:255',],

            'properties' => ['nullable', 'array'],
            'properties.*.property_id' => ['required', 'exists:properties,id', 'distinct'],
            'properties.*.value' => ['required', 'string', 'max:255'],
        ];
    }
}
