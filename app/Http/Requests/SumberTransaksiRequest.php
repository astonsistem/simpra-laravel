<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SumberTransaksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sumber_id' => 'required|string',
            'sumber_nama' => 'required|string',
            'sumber_jenis' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'sumber_id.required'       => 'sumber_id harus diisi.',
            'sumber_nama.required'     => 'sumber_nama harus diisi.',
            'sumber_jenis'             => 'required|string',
        ];
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
