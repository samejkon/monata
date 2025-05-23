<?php

namespace App\Http\Requests\RoomType;

use App\Base\FormRequest;

class SearchRoomTypeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'keyword' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}
