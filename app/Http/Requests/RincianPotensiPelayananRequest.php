<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RincianPotensiPelayananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'piutang_id'     => 'nullable|string',
            'pendaftaran_id' => 'required|integer',
            'total_tagihan'  => 'required|numeric',
            'total_klaim'    => 'required|numeric',
            'total_verif'    => 'required|numeric',
            'total_bayar'    => 'required|numeric',
            'jenis'          => 'required|string',
            'bulan'          => 'required|integer',
            'tahun'          => 'required|integer',
            'penjamin_id'    => 'required|integer',
            'sumber'         => 'required|string',
            'sep'            => 'required|string',
            'norm'           => 'required|string',
            'nama'           => 'required|string',
            'tgl_mrs'        => 'required|string',
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
