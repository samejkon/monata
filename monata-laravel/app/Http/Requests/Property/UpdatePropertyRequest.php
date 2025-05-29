<?php

namespace App\Http\Requests\Property;

use App\Base\FormRequest;
use Illuminate\Validation\Rule;

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
                Rule::unique('properties', 'name')->ignore($this->route('property')),
            ],
        ];
    }
}
