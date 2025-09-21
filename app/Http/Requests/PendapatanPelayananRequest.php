<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PendapatanPelayananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis_tagihan'       => 'required|string',
            'pasien_nama'         => 'required|string',
            'no_rekam_medik'      => 'required|string',
            'pasien_alamat'       => 'nullable|string',
            'no_pendaftaran'      => 'required|string',
            'tgl_pendaftaran'     => 'required|string',
            'tgl_krs'             => 'nullable|string',
            'tgl_pelayanan'       => 'required|string',
            'total'               => 'nullable|int',
            'total_dijamin'       => 'nullable|int',
            'total_sharing'       => 'nullable|int',
            'piutang_perorangan'  => 'nullable|int',
            'koreksi_sharing'     => 'nullable|int',
            'carabayar_id'        => 'required|int',
            'penjamin_id'         => 'required|int',
            'instalasi_id'        => 'nullable|int',
            'loket_id'            => 'nullable|int',
            'kasir_id'            => 'nullable|int',
            'pendapatan'          => 'nullable|int',
            'piutang'             => 'nullable|int',
            'pdd'                 => 'nullable|int',
            'biaya_admin'         => 'nullable|int',
            'obat_dijamin'        => 'nullable|int',
            'status_fase1'        => 'nullable|string',
            'status_fase2'        => 'nullable|string',
            'status_fase3'        => 'nullable|string',
            'is_valid'            => 'required|boolean',
            'is_penjaminlebih1'   => 'required|boolean',
            'is_naikkelas'        => 'required|boolean',
            'hak_kelasrawat'      => 'nullable|string',
            'naik_kelasrawat'     => 'nullable|string',
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
