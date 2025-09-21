<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PendapatanPenjamin1Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pelayanan_id'      => 'required|string',
            'pendaftaran_id'    => 'required|integer',
            'no_pendaftaran'    => 'required|string',
            'tgl_pendaftaran'   => 'required|string',
            'pasien_id'         => 'required|integer',
            'jenis_tagihan'     => 'required|string',
            'tgl_krs'           => 'required|string',
            'tgl_pelayanan'     => 'required|string',
            'no_rekam_medik'    => 'required|string',
            'pasien_nama'       => 'required|string',
            'carabayar_id'      => 'required|integer',
            'penjamin_id'       => 'required|integer',
            'no_penjamin'       => 'nullable|string',
            'tgl_jaminan'       => 'nullable|string',
            'instalasi_id'      => 'required|integer',
            'kasir_id'          => 'required|integer',
            'loket_id'          => 'required|integer',
            'total_dijamin'     => 'required|numeric',
            'bulan_mrs'         => 'required|string',
            'bulan_krs'         => 'required|string',
            'bulan_pelayanan'   => 'required|string',
            'biaya_admin'       => 'required|numeric',
            'status'            => 'required|string',
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
