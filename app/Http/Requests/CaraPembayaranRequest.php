<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CaraPembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bayar_id' => 'required|string',
            'bayar_nama' => 'required|string',
            'is_aktif' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'bayar_id.required'       => 'bayar_id harus diisi.',
            'bayar_nama.required'     => 'bayar_nama harus diisi.',
            'is_aktif'                => 'required|string',
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
