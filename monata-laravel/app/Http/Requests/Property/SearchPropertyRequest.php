<?php

namespace App\Http\Requests\Property;

use App\Base\FormRequest;

class SearchPropertyRequest extends FormRequest
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
