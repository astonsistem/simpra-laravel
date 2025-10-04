<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RincianBkuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bku_id'     => 'nullable|integer',
            'ket'        => 'required|string',
            'uraian'     => 'nullable|string',
            'akun_id'    => 'nullable|integer',
            'rek_id'     => 'nullable|integer',
            'jumlah'     => 'nullable|integer',
            'pendapatan' => 'nullable|integer',
            'pdd'        => 'nullable|integer',
            'piutang'    => 'nullable|integer',
            'pad_rinci'  => 'nullable|integer',
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
