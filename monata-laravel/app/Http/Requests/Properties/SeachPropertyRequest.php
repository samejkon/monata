<?php

namespace App\Http\Requests\Properties;

use App\Base\FormRequest;

class SeachPropertyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'keyword' => [
                'nullable',
                'string'
            ],
        ];
    }
}
