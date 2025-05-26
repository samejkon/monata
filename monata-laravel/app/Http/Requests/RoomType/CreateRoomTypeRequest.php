<?php

namespace App\Http\Requests\RoomType;

use App\Base\FormRequest;

class CreateRoomTypeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> The validation rules.
     */

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'unique:room_types,name', 'max:255'],

            'properties' => ['required', 'array'],
            'properties.*.property_id' => ['required', 'exists:properties,id'],
            'properties.*.value' => ['required', 'string', 'max:255'],
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
            'properties.*.id.exists' => 'Property does not exist.',
        ];
    }
}
