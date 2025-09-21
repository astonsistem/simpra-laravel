<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PotensiLainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'akun_id'            => 'required|integer',
            'besaran_per_satuan' => 'nullable|numeric',
            'induk_id'           => 'nullable|string',
            'is_buktitagihan'    => 'nullable|boolean',
            'jatuh_tempo'        => 'nullable|integer',
            'ket'                => 'nullable|string',
            'monev_id'           => 'nullable|integer',
            'nilai_reklasputus'  => 'nullable|numeric',
            'no_dokumen'         => 'nullable|string',
            'no_putus'           => 'nullable|string',
            'pembayaran_piutang' => 'nullable|numeric',
            'pihak3'             => 'nullable|string',
            'pihak3_alamat'      => 'nullable|string',
            'pihak3_telp'        => 'nullable|string',
            'reklas_pdd'         => 'nullable|numeric',
            'tgl'                => 'nullable|string',
            'tgl_akhir'          => 'nullable|string',
            'tgl_berlaku'        => 'nullable|string',
            'tgl_berlakuputus'   => 'nullable|string',
            'tgl_dokumen'        => 'required|string',
            'tgl_putus'          => 'nullable|string',
            'total'              => 'nullable|numeric',
            'total_pdd'          => 'nullable|numeric',
            'total_piutang'      => 'nullable|numeric',
            'uraian'             => 'nullable|string',
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
