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
            'pasien_alamat'       => 'required|string',
            'no_pendaftaran'      => 'required|string',
            'tgl_pendaftaran'     => 'required|string',
            'tgl_krs'             => 'required|string',
            'tgl_pelayanan'       => 'required|string',
            'total'               => 'required|int',
            'total_dijamin'       => 'required|int',
            'total_sharing'       => 'required|int',
            'piutang_perorangan'  => 'required|int',
            'koreksi_sharing'     => 'required|int',
            'carabayar_id'        => 'required|int',
            'penjamin_id'         => 'required|int',
            'instalasi_id'        => 'required|int',
            'loket_id'            => 'required|int',
            'kasir_id'            => 'required|int',
            'pendapatan'          => 'required|int',
            'piutang'             => 'required|int',
            'pdd'                 => 'required|int',
            'biaya_admin'         => 'required|int',
            'obat_dijamin'        => 'required|int',
            'status_fase1'        => 'required|string',
            'status_fase2'        => 'required|string',
            'status_fase3'        => 'required|string',
            'is_valid'            => 'required|boolean',
            'is_penjaminlebih1'   => 'required|boolean',
            'is_naikkelas'        => 'required|boolean',
            'hak_kelasrawat'      => 'required|string',
            'naik_kelasrawat'     => 'required|string',
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
