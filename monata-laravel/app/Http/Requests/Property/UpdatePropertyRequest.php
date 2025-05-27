<?php

namespace App\Http\Requests\Property;

use App\Base\FormRequest;

class UpdatePropertyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'sometimes', 'max:255'],
        ];
    }
}
