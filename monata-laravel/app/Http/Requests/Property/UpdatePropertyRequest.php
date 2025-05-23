<?php

namespace App\Http\Requests\Property;

use App\Base\FormRequest;

class UpdatePropertyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'sometimes',
                'max:255',
            ],
        ];
    }
}
