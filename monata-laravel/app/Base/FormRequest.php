<?php

namespace App\Base;

use Illuminate\Foundation\Http\FormRequest as FormRequestBase;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class FormRequest extends FormRequestBase
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'data' => null,
            'message' => 'Invalid data',
            'time' => time(),
            'errors' => $validator->errors(),
            'success' => true
        ], 422));
    }
}
