<?php

namespace App\Http\Requests\Properties;

use App\Base\FormRequest;

class UpdatePropertyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            '*.id' => [
                'required',
                'integer',
            ],
            '*.name' => [
                'required',
                'string',
                'sometimes:properties,name',
                'max:255',
            ],
        ];
    }
}
