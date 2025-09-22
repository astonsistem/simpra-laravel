<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PotensiPelayananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pendaftaran_id'     => 'required|integer',
            'no_pendaftaran'     => 'nullable|string',
            'tgl_pendaftaran'    => 'nullable|string',
            'no_rekam_medik'     => 'nullable|string',
            'pasien_nama'        => 'nullable|string',
            'pasien_alamat'      => 'nullable|string',
            'jenis_tagihan'      => 'nullable|string',
            'tgl_pelayanan'      => 'nullable|string',
            'instalasi_id'       => 'nullable|integer',
            'carabayar_id'       => 'required|integer',
            'no_pengajuan'       => 'required|string',
            'tgl_pengajuan'      => 'required|string',
            'no_dokumen'         => 'required|string',
            'tgl_dokumen'        => 'required|string',
            'uraian'             => 'required|string',
            'total_pengajuan'    => 'required|numeric',
            'total'              => 'required|numeric',
            'akun_id'            => 'required|integer',
            'penjamin_id'        => 'required|integer',
            'pelayanan_id'       => 'required|integer',
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
