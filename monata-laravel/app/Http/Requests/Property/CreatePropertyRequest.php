<?php

namespace App\Http\Requests\Property;

use App\Base\FormRequest;

class CreatePropertyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'unique:properties,name',
                'max:255',
            ],
        ];
    }
}
