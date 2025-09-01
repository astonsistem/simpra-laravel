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
            'pelayanan_id'      => 'nullable|string',
            'pendaftaran_id'    => 'required|integer',
            'no_pendaftaran'    => 'required|string',
            'tgl_pendaftaran'   => 'nullable|string',
            'pasien_id'         => 'required|integer',
            'jenis_tagihan'     => 'nullable|string',
            'tgl_krs'           => 'nullable|string',
            'tgl_pelayanan'     => 'nullable|string',
            'no_rekam_medik'    => 'nullable|string',
            'pasien_nama'       => 'nullable|string',
            'carabayar_id'      => 'nullable|integer',
            'penjamin_id'       => 'nullable|integer',
            'no_penjamin'       => 'nullable|string',
            'tgl_jaminan'       => 'nullable|string',
            'instalasi_id'      => 'nullable|integer',
            'kasir_id'          => 'nullable|integer',
            'loket_id'          => 'nullable|integer',
            'total_dijamin'     => 'nullable|numeric',
            'bulan_mrs'         => 'nullable|string',
            'bulan_krs'         => 'nullable|string',
            'bulan_pelayanan'   => 'nullable|string',
            'biaya_admin'       => 'nullable|numeric',
            'status'            => 'nullable|string',
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
