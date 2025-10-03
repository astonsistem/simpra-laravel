<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BkuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ket'     => 'nullable|string',
            'tgl_bku' => 'required|string',
            'jenis'   => 'required|integer',
            'uraian'  => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'detail' => collect($validator->errors())->map(function ($message, $field) {
                return [
                    'loc' => [$field, 0],
                    'msg' => $message[0],
                    'type' => 'validation_error'
                ];
            })->values()
        ], 422));
    }
}
